<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdicionaisIndicacao extends Model
{

    protected $table = 'adicionais_indicacao';

    use HasFactory;

    protected $fillable = [
        'user_uuid',
        'value'
    ];
}
