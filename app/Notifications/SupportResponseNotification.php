<?php

namespace App\Notifications;

use App\Models\Suporte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportResponseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Suporte $suporte;

    public function __construct(Suporte $suporte)
    {
        $this->suporte = $suporte;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('Seu chamado de suporte foi atualizado.')
                    ->action('Ver Chamado', url('/suporte'))
                    ->line('Obrigado por usar nossa plataforma!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Seu chamado de suporte foi atualizado.',
            'suporte_id' => $this->suporte->id,
            'status' => $this->suporte->status,
        ];
    }
}
