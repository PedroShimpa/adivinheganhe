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

    public $subject;
    public string $username;
    public Adivinhacoes $adivinhacao;
    public $unsubscribeUrl;
    public $trackingPixel;

    public function __construct(string $username, Adivinhacoes $adivinhacao)
    {
        $this->subject = 'Parabéns! Você acertou a adivinhação!';
        $this->username = $username;
        $this->adivinhacao = $adivinhacao;
        $user = \App\Models\User::where('username', $this->username)->first();
        $this->unsubscribeUrl = $user ? route('unsubscribe', [
            'userId' => $user->id,
            'token' => hash('sha256', $user->email . env('APP_KEY'))
        ]) : '#';
        if ($user) {
            $this->track($user->email, $this->subject);
        }
        $this->trackingPixel = $this->buildTrackingPixel();
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
            view: 'emails.acerto_usuario'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
