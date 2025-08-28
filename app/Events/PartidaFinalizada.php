<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class PartidaFinalizada implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $uuid; // UUID da partida

    /**
     * Cria um novo evento.
     *
     * @param string $uuid
     */
    public function __construct(string $uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * Canal de transmissão.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        // Canal privado da partida
        return new PrivateChannel('competitivo.partida.' . $this->uuid);
    }

    /**
     * Nome do evento no frontend
     */
    public function broadcastAs()
    {
        return 'partida.finalizada';
    }
}
