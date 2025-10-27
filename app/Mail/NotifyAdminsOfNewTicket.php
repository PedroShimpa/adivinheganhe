<?php

namespace App\Mail;

use App\Mail\Traits\Trackable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifyAdminsOfNewTicket extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, Trackable;

    public string $nome;
    public ?string $email;
    public string $categoria;
    public string $descricao;
    public string $unsubscribeUrl;

    public function __construct(string $nome, ?string $email, string $categoria, string $descricao)
    {
        $this->nome = $nome;
        $this->email = $email;
        $this->categoria = $categoria;
        $this->descricao = $descricao;
        $this->unsubscribeUrl = '#'; // Admin notification
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Novo chamado aberto no sistema',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new_ticket_notification',
            data: [
                'trackingPixel' => $this->buildTrackingPixel(),
                'unsubscribeUrl' => $this->unsubscribeUrl,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
