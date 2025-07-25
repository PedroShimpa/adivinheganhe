<?php

namespace App\Http\Controllers\Traits;

use App\Models\Adivinhacoes;
use App\Models\AdivinhacoesRespostas;
use App\Models\DicasCompras;
use DateTime;
use Illuminate\Support\Facades\Auth;

trait AdivinhacaoTrait
{
    public function customize(Adivinhacoes &$adivinhacao)
    {
        $adivinhacao->count_respostas = AdivinhacoesRespostas::where('created_at', '>', $adivinhacao->created_at)->where('adivinhacao_id', $adivinhacao->id)->count();

        if (!empty($adivinhacao->expire_at)) {
            $adivinhacao->expired_at_br = (new DateTime($adivinhacao->expire_at))->format('d/m H:i');
        }

        $adivinhacao->expired = $adivinhacao->expire_at < now();

        if (!empty($adivinhacao->dica) && $adivinhacao->dica_paga == 'S' && Auth::check()) {
            $adivinhacao->buyed = DicasCompras::where('user_id', auth()->user()->id)->where('adivinhacao_id', $adivinhacao->id)->exists();
        }
    }
}
