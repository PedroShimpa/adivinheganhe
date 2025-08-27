<?php

namespace App\Notifications;

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FriendRequestNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message'   => auth()->user()->name . ' enviou um pedido de amizade.',
            'sender_id' => auth()->id(),
            'url' => route('users.friend_requests')
        ];
    }
}
