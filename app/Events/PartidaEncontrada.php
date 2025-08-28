<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PartidaEncontrada implements ShouldBroadcast
{
    public $uuid;

    public function __construct($uuid)
    {
        $this->uuid = $uuid;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('competitivo.' . auth()->id());
    }

    public function broadcastAs()
    {
        return 'partida.encontrada';
    }
}
