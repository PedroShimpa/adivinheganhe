<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePerguntaAdivinheOMilhaoRequest;
use App\Models\AdivinheOMilhao\Adicionais;
use App\Models\AdivinheOMilhao\InicioJogo;
use App\Models\AdivinheOMilhao\Perguntas;
use App\Models\AdivinheOMilhao\Respostas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdivinheOMilhaoController extends Controller
{
    public function __construct(private Perguntas $perguntas, private Respostas $respostas, private InicioJogo $inicio_jogo) {}

    public function create(Request $request)
    {
        if (auth()->user()->isAdmin()) {

            return view('adivinhe_o_milhao.create_pergunta');
        }
        return redirect()->route('home');
    }

    public function store(StorePerguntaAdivinheOMilhaoRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('arquivo')) {
            $arquivo = $request->file('arquivo');
            $ext = strtolower($arquivo->getClientOriginalExtension());
            $hash = Str::random(10);

            $fileName = $hash . '_' . time() . '.' . $ext;
            $filePath = 'arquivos_adivinhe_o_milhao/' . $fileName;

            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $fileName = $hash . '_' . time() . '.webp';
                $filePath = 'arquivos_adivinhe_o_milhao/' . $fileName;

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

    public function index()
    {
        return view('adivinhe_o_milhao.index')->with(['title' => "Adivinhe o Milhão"]);
    }

    public function iniciar(Request $request)
    {
        $jogando = $this->jogando($request);
        if (!$jogando) {
            if (!$this->inicio_jogo->where('user_id', $request->user()->id)->whereDate('created_at', today())->exists() || Adicionais::where('user_uuid', auth()->user()->uuid)->value('value') ?? 0 > 0) {

                if ($this->inicio_jogo->where('user_id', $request->user()->id)->whereDate('created_at', today())->exists()) {
                    Adicionais::where('user_uuid', auth()->user()->uuid)->first()->decrement('value');
                }
                $this->inicio_jogo->create(['user_id' => $request->user()->id]);
                return redirect()->route('adivinhe_o_milhao.pergunta');
            } else {
                return view('adivinhe_o_milhao.finalizado');
            }
        } else {
            return redirect()->route('adivinhe_o_milhao.pergunta');
        }
    }

    public function pergunta(Request $request)
    {
        $jogo = $this->jogando($request);

        if (empty($jogo)) {
            return view('adivinhe_o_milhao.finalizado');
        }

        $tempoRestante = (int) max(0, 600 - now()->diffInSeconds($jogo->created_at));

        $pergunta = Cache::get('pergunta_atual' . $request->user()->id);

        if (empty($pergunta)) {
            $pergunta = $this->perguntas->buscarPerguntaJogador($request->user()->id);
            Cache::put('pergunta_atual' . $request->user()->id, $pergunta, $tempoRestante);
        }

        return view('adivinhe_o_milhao.pergunta')
            ->with('pergunta', $pergunta)
            ->with('tempoRestante', $tempoRestante);
    }

    public function voce_ganhou()
    {
        return view('adivinhe_o_milhao.voce_ganhou');
    }

    public function responder(Request $request)
    {
        $jogo = $this->jogando($request);

        if (empty($jogo)) {
            return view('adivinhe_o_milhao.finalizado');
        }

        $correta = mb_strtolower($this->perguntas->where('id', $request->input('pergunta_id'))->value('resposta')) ==  mb_strtolower($request->input('resposta'));

        $this->respostas->create(['user_id' => $request->user()->id, 'resposta' => $request->input('resposta'), 'pergunta_id' => $request->input('pergunta_id'), 'correta' => $correta]);

        if ($correta) {
            $this->jogando($request)->increment('respostas_corretas');
            Cache::delete('pergunta_atual' . $request->user()->id);

            if ($this->jogando($request)->respostas_corretas >= 100) {
                return redirect()->route('adivinhe_o_milhao.voce_ganhou');
            }

            return redirect()->route('adivinhe_o_milhao.pergunta');
        }

        if ($this->jogando($request)) {

            $this->jogando($request)->update(['finalizado' => 1]);
        }

        return redirect()->route('adivinhe_o_milhao.errou');
    }

    public function errou()
    {
        return view('adivinhe_o_milhao.errou');
    }

    private function jogando(Request $request)
    {
        return $this->inicio_jogo->where('user_id', $request->user()->id)->where('created_at', '>=', now()->subMinutes(10))->where('finalizado', 0)->first();
    }
}
