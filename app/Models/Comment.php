<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes;

    protected  $fillable = [
        'user_id',
        'body'
    ];

    protected $casts = [
        'body' => 'string'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($comment) {
            if (strlen($comment->body) > 250) {
                $comment->body = substr($comment->body, 0, 250);
            }
        });
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
