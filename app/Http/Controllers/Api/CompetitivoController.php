<?php

namespace App\Http\Controllers\Api;

use App\Events\PartidaEncontrada;
use App\Http\Controllers\Controller;
use App\Models\Adivinhacoes;
use App\Models\AdivinhacoesPremiacoes;
use App\Models\AdivinheOMilhao\Perguntas;
use App\Models\Competitivo\Fila;
use App\Models\Competitivo\Partidas;
use App\Models\Competitivo\PartidasJogadores;
use App\Models\Competitivo\Respostas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CompetitivoController extends Controller
{
    public function __construct(private AdivinhacoesPremiacoes $adivinhacoesPremiacoes, private Adivinhacoes $adivinhacoes) {}

    public function iniciarBusca(Request $request)
    {
        $user = Auth::user();

        if (Fila::where('user_id', $user->id)->where('status', 0)->exists()) {
            return response()->json(['message' => 'Você já está buscando partida.'], 422);
        }
        $partida = Partidas::where('status', 1)->whereHas('jogadores', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->first();
        if ($partida) {
            broadcast(new PartidaEncontrada($partida->uuid, $user->id));
            return;
        }

        Fila::create([
            'user_id' => $user->id,
            'elo' => $user->getOrCreateRank()->elo ?? 200,
            'status' => 0
        ]);

        $this->matchmaking($user);

        return response()->json(['sucess' => true, 'message' => 'Busca iniciada']);
    }

    public function sairFila(Request $request)
    {
        $user = Auth::user();

        Fila::where('user_id', $user->id)
            ->where('status', 0)
            ->delete();
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
            'dificuldade_atual' => 1
        ]);

        PartidasJogadores::insert([
            ['partida_id' => $partida->id, 'user_id' => $user->id],
            ['partida_id' => $partida->id, 'user_id' => $adversario->user_id],
        ]);

        $partida->increment('round_atual');
        $partida->round_started_at = now();
        $partida->save();
        $this->newPergunta($partida);

        broadcast(new PartidaEncontrada($partida->uuid, $user->id, $adversario->user_id));
    }

    public function partida(Partidas $partida)
    {
        return response()->json(['partida' => $partida]);
    }

    public function buscar_pergunta(Partidas $partida)
    {
        if ($partida->status == 1) {
            $pergunta = Cache::get('pergunta_atual_partida' . $partida->id);
            return response()->json($pergunta);
        }
        return null;
    }

    public function newPergunta(Partidas $partida)
    {
        $pergunta = Perguntas::select('competitivo_perguntas.pergunta', 'competitivo_perguntas.id', 'arquivo')->leftJoin('competitivo_respostas', function ($join) use ($partida) {
            $join->on('competitivo_respostas.pergunta_id', '=', 'competitivo_perguntas.id')->where('competitivo_respostas.partida_id', '=', $partida->id);
        })->where('dificuldade', $partida->dificuldade_atual)->whereNull('competitivo_respostas.id')->inRandomOrder()->first();

        $pergunta->round_started_at = $partida->round_started_at;

        Cache::put('pergunta_atual_partida' . $partida->id, $pergunta);
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
            ->where('round_atual', $partida->round_atual)
            ->exists();

        if ($jaRespondeu) return null;

        $resposta = strtolower($request->input('resposta'));
        $correta = $resposta == strtolower($pergunta->resposta);

        Respostas::create([
            'pergunta_id' => $pergunta->id,
            'user_id' => $user->id,
            'partida_id' => $partida->id,
            'resposta' => $resposta,
            'correta' => $correta,
            'round_atual' => $partida->round_atual
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

                $partida->increment('round_atual');
                $partida->round_started_at = now();
                $partida->save();
                $this->newPergunta($partida);
                event(new \App\Events\NovaPergunta($partida->uuid));
            } elseif ($algumErrado && $totalJogadores == 2) {
                $vencedor = $respostasRodada->firstWhere('correta', true)->user_id ?? null;

                if ($vencedor) {
                    $perdedor = $respostasRodada->firstWhere('correta', false)->user_id;
                    $partida->jogadores()->where('user_id', $vencedor)->update(['vencedor' => 1]);

                    $userVencedor = User::find($vencedor)->rank;
                    if ($userVencedor) {
                        $userVencedor->elo += env('PONTOS_COMPETITVO', 5);
                        $userVencedor->save();
                    }

                    $userPerdedor = User::find($perdedor)->rank;
                    if ($userPerdedor) {
                        $userPerdedor->elo -= env('PONTOS_COMPETITVO', 5);
                        $userPerdedor->save();
                    }
                }

                $partida->status = 2;
                $partida->save();

                Cache::forget('pergunta_atual_partida' . $partida->id);
                event(new \App\Events\PartidaFinalizada($partida->uuid));
            } else {
                $partida->status = 2;
                $partida->save();

                Cache::forget('pergunta_atual_partida' . $partida->id);

                event(new \App\Events\PartidaFinalizada($partida->uuid));
            }
        }

        return response()->json(['correta' => $correta]);
    }
}
