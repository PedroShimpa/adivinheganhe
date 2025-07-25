<?php

namespace App\Trais;

use App\Models\AdicionaisIndicacao;
use App\Models\AdivinhacoesRespostas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

trait CountTrys
{
    public function count(&$trys, &$limitExceded)
    {

        if (Auth::check()) {
            $userId = auth()->id();
            $userUuid = auth()->user()->uuid;

            $countTrysToday = AdivinhacoesRespostas::where('user_id', $userId)
                ->whereDate('created_at', today())
                ->count();

            $countFromIndications = Cache::remember("indicacoes_{$userUuid}", 240, function () use ($userUuid) {
                return AdicionaisIndicacao::where('user_uuid', $userUuid)->value('value') ?? 0;
            });

            $limit = env('MAX_ADIVINHATIONS', 10) + $countFromIndications;
            $limitExceded = $countTrysToday >= $limit;
            $trys = $limit - $countTrysToday;
        }
    }
}
