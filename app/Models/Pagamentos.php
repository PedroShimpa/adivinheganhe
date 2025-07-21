<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pagamentos extends Model
{
    protected $fillable = [
        'user_id',
        'desc',
        'value',
        'client_id',
        'payment_id',
        'payment_status'
    ];
}
