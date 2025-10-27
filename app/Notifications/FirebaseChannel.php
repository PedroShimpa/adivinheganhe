<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class FirebaseChannel
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('services.firebase.credentials'))
            ->withProjectId(config('services.firebase.project_id'));

        $this->messaging = $factory->createMessaging();
    }

    public function send($notifiable, Notification $notification)
    {
        if (!$notifiable->token_push_notification) {
            return;
        }

        $message = $notification->toFirebase($notifiable);

        if (!$message) {
            return;
        }

        $firebaseMessage = CloudMessage::withTarget('token', $notifiable->token_push_notification)
            ->withNotification(FirebaseNotification::create($message['title'], $message['body']))
            ->withData($message['data'] ?? []);

        try {
            $this->messaging->send($firebaseMessage);
        } catch (\Exception $e) {
            // Log error or handle
        }
    }
}
