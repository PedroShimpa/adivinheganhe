<?php

namespace App\Http\Controllers;

use App\Models\AdicionaisIndicacao;
use App\Models\Adivinhacoes;
use App\Models\AdivinhacoesPremiacoes;
use App\Models\AdivinhacoesRespostas;
use App\Models\DicasCompras;
use DateTime;
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

        $adivinhacoes = Cache::remember('adivinhacoes_ativas', 600, function () {
            return Adivinhacoes::select('id', 'uuid', 'titulo', 'imagem', 'descricao', 'premio', 'expire_at', 'dica', 'dica_paga', 'dica_valor')
                ->where('resolvida', 'N')
                ->where('exibir_home', 'S')
                ->where(function ($q) {
                    $q->where('expire_at', '>', now());
                    $q->orWhereNull('expire_at');
                })
                ->orderBy('id', 'desc')
                ->get();
        });

        $adivinhacoes->each(function ($a) {
            if (Cache::get("respostas_adivinhacao_{$a->id}")) {
                $a->count_respostas = Cache::get("respostas_adivinhacao_{$a->id}");
            } else {
                $count = AdivinhacoesRespostas::where('adivinhacao_id', $a->id)->count();
                Cache::put("respostas_adivinhacao_{$a->id}", $count, now()->addMinutes(300));
                $a->count_respostas = $count;
            }
            if (!empty($a->expire_at)) {
                $a->expired_at_br = (new DateTime($a->expire_at))->format('d/m H:i');
            }
            $a->expired = $a->expire_at < now();

            if (!empty($a->dica) && $a->dica_paga == 'S' && Auth::check()) {
                $a->buyed = DicasCompras::where('user_id', auth()->user()->id)->where('adivinhacao_id', $a->id)->exists();
            }
        });


        $adivinhacoesExpiradas = Cache::remember('adivinhacoes_expiradas', 240, function () {
            return Adivinhacoes::select('uuid', 'titulo')
                ->where('resolvida', 'N')
                ->whereNotNull('expire_at')
                ->where('expire_at', '<', now())
                ->orderBy('expire_at', 'desc')
                ->get();
        });

        $premios = Cache::remember('premios_ultimos', 240, function () {
            return AdivinhacoesPremiacoes::select(
                'adivinhacoes_premiacoes.id',
                'adivinhacoes.uuid',
                'adivinhacoes.titulo',
                'adivinhacoes.resposta',
                'adivinhacoes.premio',
                'users.username',
                'premio_enviado',
                'previsao_envio_premio',
                'vencedor_notificado'
            )
                ->join('adivinhacoes', 'adivinhacoes.id', '=', 'adivinhacoes_premiacoes.adivinhacao_id')
                ->join('users', 'users.id', '=', 'adivinhacoes_premiacoes.user_id')
                ->orderBy('adivinhacoes_premiacoes.id', 'desc')
                ->get();
        });

        return view('home')->with(compact('adivinhacoes', 'limitExceded', 'premios', 'trys', 'adivinhacoesExpiradas'));
    }
}
