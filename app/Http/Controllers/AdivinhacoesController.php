<?php

namespace App\Http\Controllers;

use App\Models\Adivinhacoes;
use App\Http\Requests\StoreAdivinhacoesRequest;
use App\Models\AdicionaisIndicacao;
use App\Models\AdivinhacoesRespostas;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdivinhacoesController extends Controller
{

    public function index(Adivinhacoes $adivinhacao)
    {
        $trys = 0;
        $limitExceded = true;

        if (Auth::check()) {
            $userId = auth()->user()->id;
            $userUuid = auth()->user()->uuid;

            $countTrysToday = AdivinhacoesRespostas::where('user_id', $userId)
                ->whereDate('created_at', today())
                ->count();

            $countFromIndications = Cache::remember("indicacoes_{$userUuid}", 240, function () use ($userUuid) {
                return AdicionaisIndicacao::where('user_uuid', $userUuid)->value('value') ?? 0;
            });

            $limitExceded = $countTrysToday >= (env('MAX_ADIVINHATIONS', 10) + $countFromIndications);
            $trys = (env('MAX_ADIVINHATIONS', 10) + $countFromIndications) - $countTrysToday;
        }


        $adivinhacao->count_respostas = Cache::remember("respostas_adivinhacao_{$adivinhacao->id}", 60, function () use ($adivinhacao) {
            return AdivinhacoesRespostas::where('adivinhacao_id', $adivinhacao->id)->count();
        });
        if (!empty($adivinhacao->expire_at)) {
            $adivinhacao->expired_at_br = (new DateTime($adivinhacao->expire_at))->format('d/m H:i');
        }

        $adivinhacao->expired = $adivinhacao->expired_at < now();
        $respostas = [];
        if ($adivinhacao->resolvida == 'S') {

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

        return view('adivinhacoes.index')->with(compact('adivinhacao', 'trys', 'limitExceded', 'respostas'));
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
            return redirect()->route('home');
        }
        return redirect()->route('home');
    }
}
