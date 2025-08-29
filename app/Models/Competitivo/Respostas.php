<?php

namespace App\Models\Competitivo;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Respostas extends Model
{
    protected $table = 'competitivo_respostas';

    protected $fillable = [
        'pergunta_id',
        'user_id',
        'resposta',
        'correta',
        'partida_id',
        'round_atual'
    ];

    public function pergunta()
    {
        return $this->belongsTo(Perguntas::class, 'pergunta_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
