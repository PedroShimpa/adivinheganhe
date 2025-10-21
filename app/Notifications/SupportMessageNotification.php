<?php

namespace App\Notifications;

use App\Models\Suporte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $suporte;
    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Suporte $suporte, string $message)
    {
        $this->suporte = $suporte;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('Você recebeu uma nova mensagem do suporte.')
                    ->line('Chamado: #' . $this->suporte->id)
                    ->line('Mensagem: ' . $this->message)
                    ->action('Ver Chamado', route('suporte.user.show', $this->suporte))
                    ->line('Obrigado por usar nosso sistema!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Nova mensagem do suporte',
            'message' => 'Você recebeu uma nova mensagem sobre o chamado #' . $this->suporte->id,
            'url' => route('suporte.user.show', $this->suporte),
            'suporte_id' => $this->suporte->id,
        ];
    }
}
