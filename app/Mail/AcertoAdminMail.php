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

    public $subject;
    public User $usuario;
    public Adivinhacoes $adivinhacao;
    public $unsubscribeUrl;

    public function __construct(User $usuario, Adivinhacoes $adivinhacao)
    {
        $this->subject = 'Um usuário acertou uma adivinhação!';
        $this->usuario = $usuario;
        $this->adivinhacao = $adivinhacao;
        $this->unsubscribeUrl = route('unsubscribe', [
            'userId' => $this->usuario->id,
            'token' => hash('sha256', $this->usuario->email . env('APP_KEY'))
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject
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
