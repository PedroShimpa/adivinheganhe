<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdivinhacoesRespostas extends Model
{
    protected $table = 'adivinhacoes_respostas';

    protected $fillable = [
        'user_id',
        'adivinhacao_id',
        'resposta'
    ];
}
