<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\FirebaseChannel;

class ProfileVisitNotification extends Notification
{
    use Queueable;

    public function __construct(private string $visitorName) {}

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
            'message' => $this->visitorName . ' visitou seu perfil.',
            'sender_id' => null, // No specific sender ID for visits
            'url' => route('profile.view', $notifiable->username)
        ];
    }

    public function toFirebase($notifiable)
    {
        return [
            'title' => 'Visita ao perfil',
            'body' => $this->visitorName . ' visitou seu perfil.',
            'data' => [
                'url' => route('profile.view', $notifiable->username)
            ],
        ];
    }
}
