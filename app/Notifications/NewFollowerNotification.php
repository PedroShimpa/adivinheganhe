<?php

namespace App\Notifications;

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewFollowerNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message'   => auth()->user()->username . ' agora está te seguindo.',
            'sender_id' => auth()->id(),
            'url' => route('profile.view', auth()->user()->username)
        ];
    }
}

