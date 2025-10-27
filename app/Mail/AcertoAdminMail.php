<?php

namespace App\Mail;

use App\Mail\Traits\Trackable;
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
    use Queueable, SerializesModels, Trackable;

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
        $unsubscribeUrl = route('unsubscribe', [
            'userId' => $this->usuario->id,
            'token' => hash('sha256', $this->usuario->email . env('APP_KEY'))
        ]);

        return new Content(
            view: 'emails.acerto_admin',
            with: [
                'trackingPixel' => $this->buildTrackingPixel(),
                'unsubscribeUrl' => $unsubscribeUrl,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
