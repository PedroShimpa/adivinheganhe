<?php

// app/Events/ChatMessageSent.php
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

    public function __construct($message, $senderId, $senderName, $receiverId)
    {
        $this->message = $message;
        $this->senderName = $senderName;
        $this->senderId = $senderId;
        $this->receiverId = $receiverId;
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
