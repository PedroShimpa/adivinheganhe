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

    public $user_photo;
    public $usuario;
    public $adivinhacaoId;
    public $body;

    public function __construct($userPhoto = null, string $usuario, int $adivinhacaoId, string $body)
    {
        $this->user_photo = $userPhoto;
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
