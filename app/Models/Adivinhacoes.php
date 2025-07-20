<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adivinhacoes extends Model
{
    /** @use HasFactory<\Database\Factories\AdivinhacoesFactory> */
    use HasFactory;

    protected $fillable = [
        'titulo',
        'imagem',
        'descricao',
        'premio',
        'resposta',
        'resolvida'
    ];
}
