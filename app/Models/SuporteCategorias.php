<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuporteCategorias extends Model
{
    protected $table = 'suporte_categorias';

    protected $fillable = [
        'descricao',
    ];
}
