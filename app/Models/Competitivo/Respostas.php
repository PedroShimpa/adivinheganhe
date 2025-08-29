<?php

namespace App\Models\Competitivo;

use Illuminate\Database\Eloquent\Model;

class Respostas extends Model
{
    protected $table = 'competitivo_respostas';

    protected $fillable = [
        'pergunta_id',
        'user_id',
        'resposta',
        'correta',
        'partida_id',
        'round_atual'
    ];
}
