<?php

namespace App\Http\Controllers;

use App\Events\BuscarPartida;
use App\Events\PartidaEncontrada;
use App\Http\Requests\CreatePerguntaCompetitivoRequest;
use App\Models\Competitivo\Fila;
use App\Models\Competitivo\Partidas;
use App\Models\Competitivo\PartidasJogadores;
use App\Models\Competitivo\Perguntas;
use App\Models\Competitivo\Respostas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompetitivoController extends Controller
{
    public function index()
    {
        return view('competitivo.index');
    }

    public function store_pergunta(CreatePerguntaCompetitivoRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('arquivo')) {
            $arquivo = $request->file('arquivo');
            $ext = strtolower($arquivo->getClientOriginalExtension());
            $hash = Str::random(10);

            $fileName = $hash . '_' . time() . '.' . $ext;
            $filePath = 'aquivos_competitivo/' . $fileName;

            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $fileName = $hash . '_' . time() . '.webp';
                $filePath = 'aquivos_competitivo/' . $fileName;

                $image = \Intervention\Image\Laravel\Facades\Image::read($arquivo)
                    ->encodeByExtension('webp', 85);

                Storage::disk('s3')->put($filePath, (string) $image);
            } else {
                Storage::disk('s3')->putFileAs('arquivos_adivinhe_o_milhao', $arquivo, $fileName);
            }

            $urlArquivo = Storage::disk('s3')->url($filePath);
            $data['arquivo'] = $urlArquivo;
        }

        Perguntas::create($data);

        return redirect()->back();
    }

    public function iniciarBusca(Request $request)
    {
        $user = Auth::user();

        if (Fila::where('user_id', $user->id)->where('status', 0)->exists()) {
            return response()->json(['message' => 'Você já está buscando partida.']);
        }

        Fila::create([
            'user_id' => $user->id,
            'elo' => $user->rank->elo,
            'status' => 0
        ]);

        broadcast(new BuscarPartida($user->id))->toOthers();

        $this->matchmaking($user);

        return response()->json(['message' => 'Busca iniciada']);
    }

    public function sairFila(Request $request)
    {
        $user = Auth::user();

        $fila = Fila::where('user_id', $user->id)
            ->where('status', 0)
            ->first();

        if ($fila) {
            $fila->delete();
            return response()->json(['message' => 'Você saiu da fila.']);
        }

        return response()->json(['message' => 'Você não estava na fila.'], 404);
    }

    private function matchmaking($user)
    {
        $oponentes = Fila::where('status', 0)
            ->where('user_id', '!=', $user->id)
            ->orderByRaw('ABS(elo - ?)', [$user->rank->elo])
            ->get();

        if ($oponentes->isEmpty()) {
            return;
        }

        $adversario = $oponentes->first();

        Fila::whereIn('user_id', [$user->id, $adversario->user_id])->delete();

        $partida = Partidas::create([
            'uuid' => Str::uuid(),
            'status' => 1,
        ]);


        PartidasJogadores::insert([
            ['partida_id' => $partida->id, 'user_id' => $user->id],
            ['partida_id' => $partida->id, 'user_id' => $adversario->user_id],
        ]);

        broadcast(new PartidaEncontrada($partida->uuid))->toOthers();
    }

    public function partida(Partidas $partida)
    {
        if (!empty($partida->jogadores->where('user_id', auth()->user()->id)->exists())) {

            if ($partida->status != 2) {

                return view('competitivo.partida');
            } else {

                return view('competitivo.partida_finalizada');
            }
        }
        return redirect()->route('home');
    }

    public function buscar_pergunta(Partidas $partida)
    {
        if ($partida->status == 1) {
            $pergunta = Cache::get('pergunta_atual_partida' . $partida->uuid);

            if (empty($pergunta)) {
                $pergunta = Perguntas::select('competitivo_perguntas.pergunta', 'competitivo_perguntas.id', 'arquivo')
                    ->leftJoin('competitivo_respostas', function ($join) use ($partida) {
                        $join->on('competitivo_respostas.pergunta_id', '=', 'competitivo_perguntas.id')
                            ->where('competitivo_respostas.partida_id', '=', $partida->id);
                    })
                    ->where('dificuldade', $partida->dificuldade_atual)
                    ->whereNull('competitivo_respostas.id')
                    ->inRandomOrder()
                    ->first();

                $partida->increment('round_atual');
                $partida->save();
                Cache::put('pergunta_atual_partida' . $partida->uuid, $pergunta);
            }
            return response()->json($pergunta);
        }
        return null;
    }

    public function responder(Request $request, Partidas $partida, Perguntas $pergunta)
    {
        if ($partida->status != 1) {
            return null;
        }

        $user = auth()->user();

        $jaRespondeu = Respostas::where('partida_id', $partida->id)
            ->where('pergunta_id', $pergunta->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($jaRespondeu) return null;

        $resposta = strtolower($request->input('resposta'));
        $correta = $resposta == strtolower($pergunta->resposta);

        Respostas::create([
            'pergunta_id' => $pergunta->id,
            'user_id' => $user->id,
            'partida_id' => $partida->id,
            'resposta' => $resposta,
            'correta' => $correta
        ]);

        $totalJogadores = $partida->jogadores->count();
        $totalRespostas = Respostas::where('partida_id', $partida->id)
            ->where('pergunta_id', $pergunta->id)
            ->count();

        if ($totalRespostas >= $totalJogadores) {

            $respostasRodada = Respostas::where('partida_id', $partida->id)
                ->where('pergunta_id', $pergunta->id)
                ->get();

            $todosCorretos = $respostasRodada->every(fn($r) => $r->correta);
            $algumErrado = $respostasRodada->contains(fn($r) => !$r->correta);

            if ($todosCorretos) {
                $partida->dificuldade_atual = min($partida->dificuldade_atual + 1, 10);
                $partida->save();

                Cache::forget('pergunta_atual_partida' . $partida->uuid);
                event(new \App\Events\NovaPergunta($partida->uuid));
            } elseif ($algumErrado && $totalJogadores == 2) {
                $vencedor = $respostasRodada->firstWhere('correta', true)->user_id ?? null;

                if ($vencedor) {
                    $partida->jogadores->where('user_id', $vencedor)->update(['vencedor' => 1]);
                }

                $partida->status = 2;
                $partida->save();

                Cache::forget('pergunta_atual_partida' . $partida->uuid);
                event(new \App\Events\PartidaFinalizada($partida->uuid));
            } else {
                $partida->status = 2;
                $partida->save();

                Cache::forget('pergunta_atual_partida' . $partida->uuid);
                event(new \App\Events\PartidaFinalizada($partida->uuid));
            }
        }

        return response()->json(['correta' => $correta]);
    }
}
