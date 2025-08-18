<?php

namespace App\Models\AdivinheOMilhao;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;

class InicioJogo extends Model
{

    use Cachable;

    const UPDATED_AT = NULL;
    protected $table = 'adivinhe_o_milhao_inicio_jogo';

    protected $fillable = [
        'user_id',
        'respostas_corretas',
        'finalizado'
    ];
}
