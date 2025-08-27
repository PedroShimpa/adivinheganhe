<?php

namespace App\Notifications;

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCommnetNotification extends Notification
{
    use Queueable;

    public function __construct(private string $comment, private int $postId) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => auth()->user()->username . ' comentou em seu post',
            'sender_id' => auth()->id(),
            'url' => route('posts.single',  $this->postId)
        ];
    }
}
