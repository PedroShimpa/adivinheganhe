<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdicionaisIndicacao extends Model
{
    use HasFactory;

    protected $table = 'adicionais_indicacao';

    protected $fillable = [
        'user_uuid',
        'value'
    ];
}
