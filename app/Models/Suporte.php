<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suporte extends Model
{
    protected $table = 'suporte';

    protected $fillable = [
        'nome',
        'email',
        'user_id',
        'categoria_id',
        'descricao',
        'status'
    ];
}
