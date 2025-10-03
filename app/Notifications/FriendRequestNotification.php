<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\FirebaseChannel;

class FriendRequestNotification extends Notification
{
    use Queueable;

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
            'message'   => auth()->user()->username . ' enviou um pedido de amizade.',
            'sender_id' => auth()->id(),
            'url' => route('users.friend_requests')
        ];
    }

    public function toFirebase($notifiable)
    {
        return [
            'title' => 'Novo pedido de amizade',
            'body' => auth()->user()->username . ' enviou um pedido de amizade.',
            'data' => [
                'url' => route('users.friend_requests')
            ],
        ];
    }
}
