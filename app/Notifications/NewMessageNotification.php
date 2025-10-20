<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\FirebaseChannel;

class NewMessageNotification extends Notification
{
    use Queueable;

    public function __construct(private string $message) {}

    public function via($notifiable)
    {
        $channels = ['database'];
        if ($notifiable->token_push_notification) {
            $channels[] = FirebaseChannel::class;
        }
        return $channels;
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => auth()->user()->username . ' enviou uma mensagem: ' . $this->message,
            'sender_id' => auth()->id(),
            'url' => '/conversas' // Direct URL instead of route
        ];
    }

    public function toFirebase($notifiable)
    {
        return [
            'title' => 'Nova mensagem',
            'body' => auth()->user()->username . ': ' . $this->message,
            'data' => [
                'url' => '/conversas' // Direct URL instead of route
            ],
        ];
    }
}
