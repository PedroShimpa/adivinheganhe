<?php

namespace App\Models;

use App\Models\Competitivo\PartidasJogadores;
use App\Models\Competitivo\Ranks;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class User extends Authenticatable
{
    use Cachable, Searchable;
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
        'image',
        'banned',
        'banned_info'
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
        $sent = $this->sentFriendships()->where('status', 'accepted')->with('receiver')->get()->pluck('receiver');
        $received = $this->receivedFriendships()->where('status', 'accepted')->with('sender')->get()->pluck('sender');
        return $sent->merge($received);
    }

    public function onlineFriends()
    {
        $friends = $this->friends();

        $friendIds = $friends->pluck('id')->toArray();

        $onlineIds = DB::table('sessions')
            ->whereIn('user_id', $friendIds)
            ->pluck('user_id')
            ->unique()
            ->toArray();

        return $friends->filter(fn($friend) => in_array($friend->id, $onlineIds));
    }

    public function getUunreadMessagesCount()
    {
        return ChatMessages::whereNull('chat_messages.read_at')->where('receiver_id', $this->id)
            ->select('user_id', DB::raw('COUNT(chat_messages.id) as unread_count'))
            ->groupBy('user_id')
            ->get();
    }

    public function partidas()
    {
        return $this->hasMany(PartidasJogadores::class, 'user_id', 'id')
            ->whereHas('partida', function ($query) {
                $query->where('status', 2);
            });
    }

    public function partidaEmAndamento()
    {
        return $this->hasOne(PartidasJogadores::class, 'user_id', 'id')
            ->whereHas('partida', function ($query) {
                $query->where('status', 1);
            });
    }

    public function friendsRelation()
    {
        return $this->sentFriendships()->where('status', 'accepted')
            ->with('receiver')
            ->union(
                $this->receivedFriendships()->where('status', 'accepted')
                    ->select('receiver_id as sender_id', 'sender_id as receiver_id', 'status', 'created_at', 'updated_at') // necessário para union
            );
    }

    public function notifications()
    {
        return $this->morphMany(\Illuminate\Notifications\DatabaseNotification::class, 'notifiable')->orderBy('created_at', 'desc');
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

        $followingIds = Follower::where('user_id', $this->id)->where('followable_type', 'App\Models\User')->pluck('followable_id')->toArray();

        $userIds = array_merge($friendIds, $followingIds, [$this->id]);

        return Post::whereIn('user_id', $userIds)
            ->latest()
            ->with('user')
            ->paginate(20);
    }

    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
            'username' => $this->username,
            'bio' => $this->bio,
            'perfil_privado' => $this->perfil_privado,
        ];
    }
    public function rank()
    {
        return $this->hasOne(Ranks::class, 'user_id');
    }

    public function getOrCreateRank()
    {
        $rank = $this->rank()->first();

        if (!$rank) {
            $rank = $this->rank()->create([
                'user_id' => $this->id,
                'elo' => 200,
            ]);
        }

        return $rank;
    }
}
