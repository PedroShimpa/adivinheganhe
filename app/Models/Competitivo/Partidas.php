<?php

namespace App\Models\Competitivo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Partidas extends Model
{
    protected $table = 'competitivo_partidas';

    protected $fillable = [
        'status',
        'round_atual',
        'tempo_atual',
        'dificuldade_atual',
        'uuid',
        'round_started_at'
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function jogadores()
    {
        return $this->hasMany(PartidasJogadores::class, 'partida_id');
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
