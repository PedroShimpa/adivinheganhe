<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{

    protected $table = 'followers';

    protected  $fillable = [
        'user_id',
    ];

    public function followable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
