<?php

namespace App\Models;


use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;


class User extends Authenticatable
{
    use Cachable;
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'cpf',
        'whatsapp',
        'indicated_by',
        'is_admin',
        'fingerprint',
        'perfil_privado',
        'bio',
        'image'
    ];

    public function getRouteKeyName()
    {
        return 'username';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function isAdmin()
    {
        return $this->is_admin == 'S';
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id', 'id')->orderBy('id', 'desc');
    }

    public function followers()
    {
        return $this->morphMany(Follower::class, 'followable');
    }

    public function sentFriendships()
    {
        return $this->hasMany(Friendship::class, 'sender_id');
    }

    public function receivedFriendships()
    {
        return $this->hasMany(Friendship::class, 'receiver_id');
    }

    public function friends()
    {
        return $this->sentFriendships()
            ->where('status', 'accepted')
            ->with('receiver')
            ->get()
            ->merge(
                $this->receivedFriendships()
                    ->where('status', 'accepted')
                    ->with('sender')
                    ->get()
            );
    }

    public function notifications()
    {
        return $this->morphMany(\Illuminate\Notifications\DatabaseNotification::class, 'notifiable');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }

    public function unreadNotificationsCount()
    {
        return $this->unreadNotifications()->count();
    }

    public function feedPosts()
    {
        $friendIds = $this->friends()->pluck('id')->toArray();

        $followingIds = $this->followers()
            ->where('follower_id', $this->id) 
            ->pluck('followable_id')
            ->toArray();

        $userIds = array_merge($friendIds, $followingIds, [$this->id]); 

        return Post::whereIn('user_id', $userIds)
            ->latest()
            ->with('user') 
            ->paginate(20);
    }
}
