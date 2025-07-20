<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdivinhacoesPremiacoes extends Model
{
    protected $table = 'adivinhacoes_premiacoes';

    protected $fillable = [
        'user_id',
        'adivinhacao_ad',
        'premio_enviado',
    ];
}
