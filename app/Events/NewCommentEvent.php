<?php

namespace App\Events;

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class NewCommentEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public $usuario;
    public $adivinhacaoId;
    public $body;

    public function __construct(string $usuario, int $adivinhacaoId, string $body)
    {
        $this->usuario = $usuario;
        $this->adivinhacaoId = $adivinhacaoId;
        $this->body = $body;
    }

    public function broadcastOn()
    {
        return new Channel('comments');
    }

    public function broadcastAs()
    {
        return 'novoComentario';
    }
}

