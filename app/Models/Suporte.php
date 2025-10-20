<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suporte extends Model
{
    protected $table = 'suporte';

    protected $fillable = [
        'nome',
        'email',
        'user_id',
        'categoria_id',
        'descricao',
        'status',
        'admin_response'
    ];

    public function categoria()
    {
        return $this->belongsTo(SuporteCategorias::class, 'categoria_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chatMessages()
    {
        return $this->hasMany(SuporteChatMessages::class);
    }
}
