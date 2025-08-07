<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;

class SuporteCategorias extends Model
{
    use Cachable;

    protected $table = 'suporte_categorias';

    protected $fillable = [
        'descricao',
    ];
}
