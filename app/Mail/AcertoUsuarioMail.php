<?php

namespace App\Mail;

use App\Models\Adivinhacoes;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AcertoUsuarioMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $username;
    public Adivinhacoes $adivinhacao;

    public function __construct(string $username, Adivinhacoes $adivinhacao)
    {
        $this->username = $username;
        $this->adivinhacao = $adivinhacao;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Parabéns! Você acertou a adivinhação!'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.acerto_usuario'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
