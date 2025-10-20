<?php

namespace App\Http\Controllers\Traits;

use App\Models\AdicionaisIndicacao;
use App\Models\Adivinhacoes;
use App\Models\AdivinhacoesRespostas;
use App\Models\DicasCompras;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

trait AdivinhacaoTrait
{
    public function customize(Adivinhacoes &$adivinhacao)
    {
        if (!empty($adivinhacao->expire_at)) {
            $adivinhacao->expired_at_br = (new DateTime($adivinhacao->expire_at))->format('d/m H:i');
        }

        $adivinhacao->expired = $adivinhacao->expire_at < now();

        if (auth()->user()) {
            $this->verifyLimitExceded($adivinhacao->id, $adivinhacao);
        }

        if (!empty($adivinhacao->dica) && $adivinhacao->dica_paga == 'S' && Auth::check()) {
            $adivinhacao->buyed = DicasCompras::where('user_id', auth()->user()->id)->where('adivinhacao_id', $adivinhacao->id)->exists();
            if (!$adivinhacao->buyed) {
                $adivinhacao->dica = null;
            } else {
            
            }
        }

        $adivinhacao->resposta = null;
    }

    public function verifyLimitExceded(int $id, &$adivinhacao)
    {

        $userId = auth()->id();
        $userUuid = auth()->user()->uuid;

        $countTrysToday = Cache::remember('resposta_do_usuario_hoje_' . auth()->user()->id, 2400,  function () use ($userId) {
            return AdivinhacoesRespostas::where('user_id', $userId)
                ->whereDate('created_at', today())
                ->get();
        });

        $countTrysToday = $countTrysToday->where('adivinhacao_id', $id)->count();

        $countFromIndications = Cache::remember('adicionais_indicacao_usuario_' . auth()->user()->id, 2400, function () use ($userUuid) {
            return AdicionaisIndicacao::where('user_uuid', $userUuid)->value('value') ?? 0;
        });

        $maxAttempts = auth()->user()->isVip() ? 7 : env('MAX_ADIVINHATIONS', 10);
        $adivinhacao->limitExceded = $countTrysToday >= $maxAttempts && $countFromIndications == 0;
        $adivinhacao->palpites_restantes = ($maxAttempts - $countTrysToday) + $countFromIndications;
    }
}
