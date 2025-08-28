<?php

namespace App\Models\Competitivo;

use Illuminate\Database\Eloquent\Model;

class Ranks extends Model
{
    protected $table = 'competitivo_ranks';

    protected $fillable = [
        'user_id',
        'elo',
        'vitorias',
        'derrotas',
        'maior_streak'
    ];
}
