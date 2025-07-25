<?php

namespace App\Trais;

use App\Models\Adivinhacoes;
use App\Models\AdivinhacoesRespostas;
use App\Models\DicasCompras;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

trait TentativasTrait
{
    public function customize(Adivinhacoes &$adivinhacao)
    {
        $respostasCountKey = "respostas_adivinhacao_{$adivinhacao->id}";
        if (Cache::has($respostasCountKey)) {
            $adivinhacao->count_respostas = Cache::get($respostasCountKey);
        } else {
            $count = AdivinhacoesRespostas::where('adivinhacao_id', $adivinhacao->id)->count();
            Cache::put($respostasCountKey, $count);
            $adivinhacao->count_respostas = $count;
        }

        if (!empty($adivinhacao->expire_at)) {
            $adivinhacao->expired_at_br = (new DateTime($adivinhacao->expire_at))->format('d/m H:i');
        }

        $adivinhacao->expired = $adivinhacao->expire_at < now();

        if (!empty($adivinhacao->dica) && $adivinhacao->dica_paga == 'S' && Auth::check()) {
            $adivinhacao->buyed = DicasCompras::where('user_id', auth()->user()->id)->where('adivinhacao_id', $adivinhacao->id)->exists();
        }
    }
}
