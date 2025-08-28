<?php

namespace App\Models\Competitivo;

use Illuminate\Database\Eloquent\Model;

class Fila extends Model
{
    protected $table = 'competitivo_fila';

    protected $fillable = [
        'user_id',
        'elo',
        'status'
    ];
}
