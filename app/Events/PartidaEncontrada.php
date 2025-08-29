<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class PartidaEncontrada implements ShouldBroadcastNow
{
    public $uuid;
    public $user_id1;
    public $user_id2;

    public function __construct($uuid, $user_id1, $user_id2)
    {
        $this->uuid = $uuid;
        $this->user_id1 = $user_id1;
        $this->user_id2 = $user_id2;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('competitivo');
    }

    public function broadcastAs()
    {
        return 'partida.encontrada';
    }
}
