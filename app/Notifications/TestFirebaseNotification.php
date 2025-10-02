<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\FirebaseChannel;

class TestFirebaseNotification extends Notification
{
    use Queueable;

    protected $title;
    protected $body;
    protected $data;

    public function __construct($title, $body, $data = [])
    {
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
    }

    public function via($notifiable)
    {
        $channels = [];
        if ($notifiable->token_push_notification) {
            $channels[] = FirebaseChannel::class;
        }
        return $channels;
    }

    public function toFirebase($notifiable)
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
        ];
    }
}
