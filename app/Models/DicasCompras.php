<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DicasCompras extends Model
{
    protected $table = 'dicas_compras';

    protected $fillable = [
        'user_id',
        'adivinhacao_id',
        'pagamento_id'
    ];
    
}
