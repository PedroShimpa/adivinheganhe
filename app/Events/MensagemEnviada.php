<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class MensagemEnviada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $usuario;
    public $mensagem;

    public function __construct($usuario, $mensagem)
    {
        $this->usuario = $usuario;
        $this->mensagem = $mensagem;
    }

    public function broadcastOn()
    {
        return new Channel('chat');  
    }

    public function broadcastAs()
    {
        return 'MensagemEnviada'; 
    }
}
