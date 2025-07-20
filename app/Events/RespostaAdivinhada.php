<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class RespostaAdivinhada implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public $mensagem;

    public function __construct(public string $resposta, public int $adivinhacaoId)
    {
        $this->mensagem = "A resposta correta já foi adivinhada: {$resposta}";
    }

    public function broadcastOn()
    {
        return new Channel('adivinhacoes');
    }

    public function broadcastAs()
    {
        return 'resposta.adivinhada';
    }
}
