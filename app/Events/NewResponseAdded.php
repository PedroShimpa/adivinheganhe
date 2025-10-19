<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class NewResponseAdded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public int $totalResponses;

    public function __construct(int $totalResponses)
    {
        $this->totalResponses = $totalResponses;
    }

    public function broadcastOn()
    {
        return new Channel('dashboard-updates');
    }

    public function broadcastAs()
    {
        return 'response.added';
    }

    public function broadcastWith()
    {
        return [
            'totalResponses' => $this->totalResponses,
        ];
    }
}
