<?php

namespace App\Models\Competitivo;

use Illuminate\Database\Eloquent\Model;

class Respostas extends Model
{
    protected $table = 'competitivo_respostas';

    protected $fillable = [
        'pergunta_id',
        'resposta',
        'correta'
    ];
}
