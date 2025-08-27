<?php

namespace App\Notifications;

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCommnetNotification extends Notification
{
    use Queueable;

    public function __construct(private string $comment)
    {
        
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => auth()->user()->name . ' comentou: '. $this->comment,
            'sender_id' => auth()->id(),
        ];
    }
}

