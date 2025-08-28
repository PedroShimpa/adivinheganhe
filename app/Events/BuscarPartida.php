<?php
// BuscarPartida.php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class BuscarPartida implements ShouldBroadcast
{
    use InteractsWithSockets;

    public $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('competitivo.' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'buscar.partida';
    }
}
