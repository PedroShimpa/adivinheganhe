<?php

namespace App\Models\AdivinheOMilhao;

use Illuminate\Database\Eloquent\Model;

class Adicionais extends Model
{
    protected $table = 'adicionais_indicacao_adivinhe_o_milhao';

    protected $fillable = [
        'user_uuid',
        'value',
    ];
}
