<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifyAdminsOfNewTicket extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $subject;
    public string $nome;
    public ?string $email;
    public string $categoria;
    public string $descricao;
    public $unsubscribeUrl;

    public function __construct(string $nome, ?string $email, string $categoria, string $descricao)
    {
        $this->subject = 'Novo chamado aberto no sistema';
        $this->nome = $nome;
        $this->email = $email;
        $this->categoria = $categoria;
        $this->descricao = $descricao;
        $this->unsubscribeUrl = '#'; // Admin notification
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new_ticket_notification'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
