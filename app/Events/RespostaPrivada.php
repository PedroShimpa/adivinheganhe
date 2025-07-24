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
        public int $adivinhacaoId,
        public int $userId,
        public ?string $title,

    ) {}

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'resposta.sucesso';
    }

    public function broadcastWith()
    {
        return [
            'titulo'         => $this->title ?? 'Parabéns!',
            'mensagem'       => $this->mensagem,
            'adivinhacaoId'  => $this->adivinhacaoId,
        ];
    }
}
