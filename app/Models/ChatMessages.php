<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessages extends Model
{
    protected $table = 'chat_messages';

    protected $fillable = [
        'user_id',
        'message',
        'receiver_id'
    ];
}
