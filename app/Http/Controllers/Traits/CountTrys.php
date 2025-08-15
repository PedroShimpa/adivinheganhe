<?php

namespace App\Http\Controllers\Traits;

use App\Models\AdicionaisIndicacao;
use App\Models\AdivinhacoesRespostas;
use Illuminate\Support\Facades\Auth;

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

            $countFromIndications = AdicionaisIndicacao::where('user_uuid', $userUuid)->value('value') ?? 0;

            $limit = env('MAX_ADIVINHATIONS', 10) + $countFromIndications;
            $limitExceded = $countTrysToday >= env('MAX_ADIVINHATIONS', 10) && $countFromIndications == 0;
            $trys = $limit - $countTrysToday;
            if($trys < 0) {
                $trys = 0;
            }
        }
    }
}
