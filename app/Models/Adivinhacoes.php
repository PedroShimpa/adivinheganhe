<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Adivinhacoes extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'imagem',
        'descricao',
        'premio',
        'resposta',
        'resolvida',
        'expire_at',
        'exibir_home',
        'dica',
        'dica_paga',
        'dica_valor',
        'regiao_id',
        'visualizacoes'
    ];


    protected $dates = [
        'expire_at'
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    protected static function booted()
    {
        static::creating(function ($model) {

            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}
