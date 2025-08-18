<?php

namespace App\Models\AdivinheOMilhao;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Perguntas extends Model
{
    protected $table = 'adivinhe_o_milhao_perguntas';

    protected $fillable = [
        'uuid',
        'arquivo',
        'descricao',
        'resposta'
    ];

    public function buscarPerguntaJogador(int $userId)
    {
        return $this->select('adivinhe_o_milhao_perguntas.*')
            ->leftJoin('adivinhe_o_milhao_respostas', function ($join) use ($userId) {
                $join->on('adivinhe_o_milhao_respostas.pergunta_id', '=', 'adivinhe_o_milhao_perguntas.id')
                    ->where('adivinhe_o_milhao_respostas.user_id', '=', $userId)
                    ->whereDate('adivinhe_o_milhao_respostas.created_at', '=', now()->toDateString());
            })
            ->whereNull('adivinhe_o_milhao_respostas.id')
            ->inRandomOrder()
            ->first();
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
