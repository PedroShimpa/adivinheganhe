<?php

namespace App\Events;
// app/Events/RespostaAprovada.php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class RespostaAprovada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public string $username,
        public object $adivinhacao
    ){}

    public function broadcastOn()
    {
        return new Channel('adivinhacoes');
    }

    public function broadcastAs()
    {
        return 'resposta.aprovada';
    }

    public function broadcastWith()
    {
        return [
            'username'        => $this->username,
            'respostaCorreta' => $this->adivinhacao->resposta,
            'mensagem'        => "O usuário {$this->username} acertou a adivinhação: {$this->adivinhacao->titulo}! Resposta: {$this->adivinhacao->resposta}",
            "adivinhacaoId"   => $this->adivinhacao->id
        ];
    }
}
