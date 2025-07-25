<?php

namespace App\Http\Controllers;

use App\Events\AlertaGlobal;
use App\Models\Adivinhacoes;
use App\Http\Requests\StoreAdivinhacoesRequest;
use App\Models\AdicionaisIndicacao;
use App\Models\AdivinhacoesRespostas;
use App\Models\DicasCompras;
use App\Trais\CountTrys;
use App\Trais\TentativasTrait;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdivinhacoesController extends Controller
{
    use TentativasTrait;
    use CountTrys;

    public function index(Adivinhacoes $adivinhacao)
    {
        $trys = 0;
        $limitExceded = true;

        $this->count($trys, $limitExceded);
        $this->customize($adivinhacao);

        $respostas = collect([]);
        if ($adivinhacao->resolvida == 'S' || (!empty($adivinhacao->expire_at) && $adivinhacao->expired)) {
            $respostasKey = "adivinhacoes_expiradas_{$adivinhacao->id}_page_" . request()->get('page', 1);

            $respostas = Cache::remember($respostasKey, 3600, function () use ($adivinhacao) {
                $paginated = AdivinhacoesRespostas::select('adivinhacoes_respostas.uuid', 'users.username', 'adivinhacoes_respostas.created_at', 'resposta')
                    ->join('users', 'users.id', '=', 'adivinhacoes_respostas.user_id')
                    ->where('adivinhacao_id', $adivinhacao->id)
                    ->orderBy('adivinhacoes_respostas.created_at', 'desc')
                    ->paginate(10);

                $paginated->getCollection()->transform(function ($r) {
                    $r->created_at_br = (new DateTime($r->created_at))->format('d/m/Y H:i:s');
                    return $r;
                });

                return $paginated;
            });
        }

        $title = $adivinhacao->titulo .' - '. config('app.name');
        return view('adivinhacoes.index', compact('adivinhacao', 'trys', 'limitExceded', 'respostas', 'title'));
    }

    public function create()
    {
        if (auth()->user()->id == 1) {
            return view('adivinhacoes.create');
        }
        return redirect()->route('home');
    }

    public function store(StoreAdivinhacoesRequest $request)
    {
        if (auth()->user()->id == 1) {
            $imagem = $request->file('imagem');
            $hash = Str::random(10);
            $fileName = $hash . '_' . time() . '.' . $imagem->getClientOriginalExtension();
            $path = $imagem->storeAs('imagens_adivinhacoes', $fileName, 'public');
            $data = $request->validated();
            $data['imagem'] = $path;
            $data['descricao'] = $request->input('descricao');
            Adivinhacoes::create($data);
            Cache::delete('adivinhacoes_ativas');
            broadcast(new AlertaGlobal('Nova Adivinhação', $data['titulo'] . ' adicionada, acesse a pagina inicial para ver'))->toOthers();
            return redirect()->route('home');
        }

        return redirect()->route('home');
    }
}
