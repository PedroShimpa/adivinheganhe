<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RespostasProximas extends Model
{
    protected $table = 'respostas_proximas';

    protected $fillable = [
        'adivinhacao_id',
        'proximidade',
        'resposta',
    ];
}
