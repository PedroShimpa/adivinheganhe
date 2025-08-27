<?php

namespace App\Notifications;

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FriendRequestAcceptedNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message'   => auth()->user()->name . ' aceitou seu pedido de amizade.',
            'sender_id' => auth()->id(),
        ];
    }
}

