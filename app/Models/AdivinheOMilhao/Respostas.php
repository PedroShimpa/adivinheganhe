<?php

namespace App\Models\AdivinheOMilhao;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Respostas extends Model
{
    const UPDATED_AT = null;

    protected $table = 'adivinhe_o_milhao_respostas';

    protected $fillable = [
        'uuid',
        'user_id',
        'resposta',
        'pergunta_id',
        'correta'
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
