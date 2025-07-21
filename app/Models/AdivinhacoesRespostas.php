<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AdivinhacoesRespostas extends Model
{
    protected $table = 'adivinhacoes_respostas';

    protected $fillable = [
        'user_id',
        'adivinhacao_id',
        'resposta'
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}
