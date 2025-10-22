<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $message;
    public $senderId;
    public $receiverId;
    public $senderName;
    public $created_at;
    public $is_admin;

    public function __construct($message, $senderId, $senderName, $receiverId, $created_at, $is_admin = null)
    {
        $this->message = $message;
        $this->senderName = $senderName;
        $this->senderId = $senderId;
        $this->receiverId = $receiverId;
        $this->created_at = $created_at;
        $this->is_admin = $is_admin;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->receiverId);
    }

    public function broadcastAs()
    {
        return 'mensagem.recebida_enviada';
    }
}
