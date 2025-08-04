<?php

namespace App\Mail;

use App\Models\Adivinhacoes;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AcertoAdminMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $usuario;
    public Adivinhacoes $adivinhacao;

    public function __construct(User $usuario, Adivinhacoes $adivinhacao)
    {
        $this->usuario = $usuario;
        $this->adivinhacao = $adivinhacao;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Um usuário acertou uma adivinhação!'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.acerto_admin'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
