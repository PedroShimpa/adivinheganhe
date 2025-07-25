<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CountTrys;
use App\Http\Controllers\Traits\TentativasTrait;
use App\Models\Adivinhacoes;
use App\Models\AdivinhacoesPremiacoes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    use TentativasTrait;
    use CountTrys;

    public function index(Request $request)
    {
        $trys = 0;
        $limitExceded = true;

        $this->count($trys, $limitExceded);

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
            $this->customize($a);
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
