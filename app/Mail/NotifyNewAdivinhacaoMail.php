<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifyNewAdivinhacaoMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $titulo;
    public string $url;

    public function __construct(string $titulo, string $url)
    {
        $this->titulo = $titulo;
        $this->url = $url;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nova Adivinhação Disponível!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.nova-adivinhacao', // vamos criar isso abaixo
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
