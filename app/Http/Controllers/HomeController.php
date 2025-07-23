<?php

namespace App\Http\Controllers;

use App\Models\AdicionaisIndicacao;
use App\Models\Adivinhacoes;
use App\Models\AdivinhacoesPremiacoes;
use App\Models\AdivinhacoesRespostas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(Request $request)
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

        $adivinhacoes = Cache::remember('adivinhacoes_ativas', 120, function () {
            return Adivinhacoes::select('id', 'titulo', 'imagem', 'descricao', 'premio', 'expire_at')
                ->where('resolvida', 'N')
                ->where('exibir_home', 'S')
                ->orderBy('id', 'desc')
                ->get();
        });

        $adivinhacoes->each(function ($a) {
            $a->count_respostas = Cache::remember("respostas_adivinhacao_{$a->id}", 60, function () use ($a) {
                return AdivinhacoesRespostas::where('adivinhacao_id', $a->id)->count();
            });
            if($a->expire_at) {
                $a->expired_at_br = $a->expire_at->format('d/m H:i');
            }
            $a->expired = $a->expired_at < now();
        });

        $premios = Cache::remember('premios_ultimos', 60, function () {
            return AdivinhacoesPremiacoes::select(
                    'adivinhacoes.uuid',
                    'adivinhacoes.titulo',
                    'adivinhacoes.resposta',
                    'adivinhacoes.premio',
                    'users.username',
                    'premio_enviado'
                )
                ->join('adivinhacoes', 'adivinhacoes.id', '=', 'adivinhacoes_premiacoes.adivinhacao_id')
                ->join('users', 'users.id', '=', 'adivinhacoes_premiacoes.user_id')
                ->orderBy('adivinhacoes_premiacoes.id', 'desc')
                ->get();
        });

        return view('home')->with(compact('adivinhacoes', 'limitExceded', 'premios', 'trys'));
    }
}
