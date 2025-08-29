<?php

namespace App\Models\Competitivo;

use Illuminate\Database\Eloquent\Model;

class Perguntas extends Model
{
    protected $table = 'competitivo_perguntas';

    protected $fillable = [
        'pergunta',
        'resposta',
        'arquivo',
        'dificuldade'
    ];
}
