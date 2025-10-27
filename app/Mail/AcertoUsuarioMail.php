<?php

namespace App\Mail;

use App\Models\Adivinhacoes;
use App\Mail\Traits\Trackable;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AcertoUsuarioMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, Trackable;

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
        $user = \App\Models\User::where('username', $this->username)->first();
        $unsubscribeUrl = $user ? route('unsubscribe', [
            'userId' => $user->id,
            'token' => hash('sha256', $user->email . env('APP_KEY'))
        ]) : '#';

        return new Content(
            view: 'emails.acerto_usuario',
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
