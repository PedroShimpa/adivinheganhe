<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class RespostaPrivada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public string $mensagem,
        public int $adivinhacaoId
    ) {}

    public function broadcastOn()
    {
        // canal privado user.{id}
        return new PrivateChannel('user.' . auth()->id());
    }

    public function broadcastAs()
    {
        return 'resposta.sucesso';
    }

    public function broadcastWith()
    {
        return [
            'mensagem'       => $this->mensagem,
            'adivinhacaoId'  => $this->adivinhacaoId,
        ];
    }
}
