<?php

namespace App\Mail;

use App\Mail\Traits\Trackable;
use App\Models\Suporte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportResponseMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, Trackable;

    public Suporte $suporte;
    public string $unsubscribeUrl;

    public function __construct(Suporte $suporte)
    {
        $this->suporte = $suporte;
        $this->unsubscribeUrl = $this->suporte->user ? route('unsubscribe', [
            'userId' => $this->suporte->user->id,
            'token' => hash('sha256', $this->suporte->user->email . env('APP_KEY'))
        ]) : '#';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Atualização no seu chamado de suporte',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.support_response',
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
