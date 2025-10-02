<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\FirebaseChannel;

class FriendRequestAcceptedNotification extends Notification
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
            'message'   => auth()->user()->username . ' aceitou seu pedido de amizade.',
            'sender_id' => auth()->id(),
            'url' => route('profile.view', auth()->user()->username)
        ];
    }

    public function toFirebase($notifiable)
    {
        return [
            'title' => 'Pedido de amizade aceito',
            'body' => auth()->user()->username . ' aceitou seu pedido de amizade.',
            'data' => [
                'url' => route('profile.view', auth()->user()->username)
            ],
        ];
    }
}
