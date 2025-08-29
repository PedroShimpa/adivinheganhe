<?php

namespace App\Models\Competitivo;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PartidasJogadores extends Model
{
    protected $table = 'competitivo_partidas_jogadores';

    protected $fillable = [
        'partida_id',
        'user_id',
        'vencedor',
        'round_eliminado'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
