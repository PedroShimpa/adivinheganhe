<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuporteChatMessages extends Model
{
    protected $table = 'suporte_chat_messages';

    protected $fillable = [
        'suporte_id',
        'user_id',
        'message',
        'read_at'
    ];

    public function suporte()
    {
        return $this->belongsTo(Suporte::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
