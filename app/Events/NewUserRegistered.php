<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class NewUserRegistered implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public int $totalUsers;

    public function __construct(int $totalUsers)
    {
        $this->totalUsers = $totalUsers;
    }

    public function broadcastOn()
    {
        return new Channel('dashboard-updates');
    }

    public function broadcastAs()
    {
        return 'user.registered';
    }

    public function broadcastWith()
    {
        return [
            'totalUsers' => $this->totalUsers,
        ];
    }
}
