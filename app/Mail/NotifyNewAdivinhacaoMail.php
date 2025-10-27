<?php

namespace App\Mail;

use App\Mail\Traits\Trackable;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifyNewAdivinhacaoMail extends Mailable
{
    use Queueable, SerializesModels, Trackable;

    public string $titulo;
    public string $url;
    public $trackingPixel;
    public string $unsubscribeUrl;
    public function __construct(string $titulo, string $url)
    {
        $this->unsubscribeUrl = '#'; // Ban notification, no unsubscribe
        $this->titulo = $titulo;
        $this->url = $url;
        $this->trackingPixel = $this->buildTrackingPixel();
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
            view: 'emails.nova-adivinhacao'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
