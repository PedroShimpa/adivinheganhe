<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class AlertaGlobal implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public string $titulo,
        public string $msg,
        public string $tipo = 'warning'
    ) {}

    public function broadcastOn()
    {
        return new Channel('adivinhacoes');
    }

    public function broadcastAs()
    {
        return 'alerta.global';
    }

    public function broadcastWith()
    {
        return [
            'titulo' => $this->titulo,
            'msg' => $this->msg,
            'tipo' => $this->tipo,
        ];
    }
}
