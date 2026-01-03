<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuporteReply extends Model
{
    protected $table = 'suporte_replies';

    protected $fillable = [
        'suporte_id',
        'user_id',
        'mensagem',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
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
