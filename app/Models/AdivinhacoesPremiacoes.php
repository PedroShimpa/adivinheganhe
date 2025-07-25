<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdivinhacoesPremiacoes extends Model
{
    protected $table = 'adivinhacoes_premiacoes';

    protected $fillable = [
        'user_id',
        'adivinhacao_id',
        'premio_enviado',
        'previsao_envio_premio',
        'vencedor_notificado'
    ];
}
