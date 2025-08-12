<?php

namespace App\Http\Controllers;

use App\Events\AlertaGlobal;
use App\Models\Adivinhacoes;
use App\Http\Requests\StoreAdivinhacoesRequest;
use App\Models\AdivinhacoesRespostas;
use App\Http\Controllers\Traits\CountTrys;
use App\Http\Controllers\Traits\AdivinhacaoTrait;
use App\Http\Requests\UpdateAdivinhacoesRequest;
use App\Jobs\EnviarNotificacaoNovaAdivinhacao;
use App\Models\Regioes;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class AdivinhacoesController extends Controller
{
    use AdivinhacaoTrait;
    use CountTrys;

    public function index(Adivinhacoes $adivinhacao)
    {
        $trys = 0;
        $limitExceded = true;

        $this->count($trys, $limitExceded);
        $this->customize($adivinhacao);

        $respostas = collect([]);
        if (($adivinhacao->resolvida == 'S' || (!empty($adivinhacao->expire_at) && $adivinhacao->expired) || (Auth::check() && auth()->user()->isAdmin()))) {

            $respostas = AdivinhacoesRespostas::select('adivinhacoes_respostas.uuid', 'users.username', 'adivinhacoes_respostas.created_at', 'resposta')
                ->join('users', 'users.id', '=', 'adivinhacoes_respostas.user_id')
                ->where('adivinhacao_id', $adivinhacao->id)
                ->orderBy('adivinhacoes_respostas.created_at', 'desc')
                ->paginate(10);

            $respostas->getCollection()->transform(function ($r) {
                $r->created_at_br = (new DateTime($r->created_at))->format('d/m/Y H:i:s');
                return $r;
            });
        }

        $title = $adivinhacao->titulo . ' - ' . env('APP_NAME', 'Adivinhe e Ganhe');
        return view('adivinhacoes.index', compact('adivinhacao', 'trys', 'limitExceded', 'respostas', 'title'));
    }

    public function create()
    {
        if (auth()->user()->isAdmin()) {
            $regioes = Regioes::all();
            return view('adivinhacoes.create')->with(compact('regioes'));
        }
        return redirect()->route('home');
    }

    public function view(Adivinhacoes $adivinhacao)
    {
        if (auth()->user()->isAdmin()) {
            $regioes = Regioes::all();

            return view('adivinhacoes.view')->with(compact('adivinhacao', 'regioes'));
        }
        return redirect()->route('home');
    }

    public function update(UpdateAdivinhacoesRequest $request, $adivinhacaoId)
    {
        if (auth()->user()->isAdmin()) {
            $data = $request->validated();

            $imagem = $request->file('imagem');
            $hash = Str::random(10);
            $fileName = $hash . '_' . time() . '.webp';
            if (!empty($imagem) && !$imagem->getClientOriginalExtension() != 'webp') {

                $image = Image::read($imagem)->encodeByExtension('webp', 85);

                Storage::disk('public')->put('imagens_adivinhacoes/' . $fileName, (string) $image);

                $data['imagem'] = 'imagens_adivinhacoes/' . $fileName;
            }

            $data['descricao'] = $request->input('descricao');

            if (!empty($data['expire_at'])) {
                $data['expire_at'] = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $data['expire_at'])->format('Y-m-d H:i:s');
            }
            Adivinhacoes::where('id', $adivinhacaoId)->update($data);

            if ($request->input('regiao_id')) {
                Cache::delete('adivinhacoes_ativas_rg_' . $request->input('regiao_id'));
            } else {

                Cache::delete('adivinhacoes_ativas');
            }
            if ($request->input('enviar_alerta_global') == 'S') {
                broadcast(new AlertaGlobal('Adivinhação Editada', $data['titulo'] . ' foi atualizada, acesse a pagina inicial para ver'))->toOthers();
            }

            return redirect()->route('adivinhacoes.index', Adivinhacoes::find($adivinhacaoId)->uuid);
        }

        return redirect()->route('home');
    }


    public function store(StoreAdivinhacoesRequest $request)
    {
        if (auth()->user()->isAdmin()) {
            $data = $request->validated();

            $imagem = $request->file('imagem');
            $hash = Str::random(10);
            $fileName = $hash . '_' . time() . '.webp';

            $image = Image::read($imagem)->encodeByExtension('webp', 85);

            Storage::disk('public')->put('imagens_adivinhacoes/' . $fileName, (string) $image);

            $data['imagem'] = 'imagens_adivinhacoes/' . $fileName;
            $data['descricao'] = $request->input('descricao');


            if (!empty($data['expire_at'])) {
                $data['expire_at'] = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $data['expire_at'])->format('Y-m-d H:i:s');
            }

            $adivinhacao = Adivinhacoes::create($data);
            if ($request->input('regiao_id')) {
                Cache::delete('adivinhacoes_ativas_rg_' . $request->input('regiao_id'));
            } else {

                Cache::delete('adivinhacoes_ativas');
            }
            if ($request->input('enviar_alerta_global') == 'S') {

                broadcast(new AlertaGlobal('Nova Adivinhação', $data['titulo'] . ' adicionada, acesse a pagina inicial para ver'))->toOthers();
            }
            $titulo = $adivinhacao->titulo;
            $url = route('adivinhacoes.index', $adivinhacao->uuid);

            if ($request->input('enviar_email') == 'S') {
                dispatch(new EnviarNotificacaoNovaAdivinhacao($titulo, $url));
            }

            return redirect()->route('adivinhacoes.index', $adivinhacao->uuid);
        }

        return redirect()->route('home');
    }

    public function findUserReply(Request $request)
    {
        $respostas = AdivinhacoesRespostas::select('resposta')->where('adivinhacao_id', $request->adivinhacao_id)->where('user_id', auth()->user()->id)->get();
        return response()->json($respostas);
    }
}
