<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\FirebaseChannel;

class NewFollowerNotification extends Notification
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
            'message'   => auth()->user()->username . ' agora está te seguindo.',
            'sender_id' => auth()->id(),
            'url' => route('profile.view', auth()->user()->username)
        ];
    }

    public function toFirebase($notifiable)
    {
        return [
            'title' => 'Novo seguidor',
            'body' => auth()->user()->username . ' agora está te seguindo.',
            'data' => [
                'url' => route('profile.view', auth()->user()->username)
            ],
        ];
    }
}

