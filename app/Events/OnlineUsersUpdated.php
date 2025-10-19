<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class OnlineUsersUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public int $count;
    public array $users;

    public function __construct(int $count, array $users)
    {
        $this->count = $count;
        $this->users = $users;
    }

    public function broadcastOn()
    {
        return new Channel('dashboard-updates');
    }

    public function broadcastAs()
    {
        return 'online.users.updated';
    }

    public function broadcastWith()
    {
        return [
            'count' => $this->count,
            'users' => $this->users,
        ];
    }
}
