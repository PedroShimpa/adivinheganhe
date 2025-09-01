<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\AdivinhacaoTrait;
use App\Models\Adivinhacoes;

class AdivinhacoesController extends Controller
{
    use AdivinhacaoTrait;

    public function __construct(protected Adivinhacoes $adivinhacoes) {}

    public function getAtivas()
    {
        $adivinhacoes = $this->adivinhacoes->getAtivas()->filter(function ($a) {
            return is_null($a->expire_at) || $a->expire_at > now();
        })->values();

        $adivinhacoes->each(function ($a) {
            $this->customize($a);
        });
        return response()->json($adivinhacoes);
    }
}
