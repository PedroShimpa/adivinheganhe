<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;

class ChatMessages extends Model
{

    use Cachable;
    
    protected $table = 'chat_messages';

    protected $fillable = [
        'user_id',
        'message',
    ];
}
