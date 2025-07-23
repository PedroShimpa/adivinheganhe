<?php

namespace App\Events;

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class AumentaContagemRespostas implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public int $adivinhacaoId,
        public int $contagem
    ){}

    public function broadcastOn()
    {
        return new Channel('adivinhacoes');
    }

    public function broadcastAs()
    {
        return 'resposta.contagem';
    }

    public function broadcastWith()
    {
        return [
            'adivinhacaoId' => $this->adivinhacaoId,
            'contagem'       => $this->contagem
        ];
    }
}
