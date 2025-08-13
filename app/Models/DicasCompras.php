<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;

class DicasCompras extends Model
{
    use Cachable;
    
    protected $table = 'dicas_compras';

    protected $fillable = [
        'user_id',
        'adivinhacao_id',
        'pagamento_id'
    ];
    
}
