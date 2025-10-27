<?php

namespace App\Mail;

use App\Mail\Traits\Trackable;
use App\Models\Suporte;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportMessageMail extends Mailable
{
    use Queueable, SerializesModels, Trackable;

    public $suporte;
    public $message;
    public string $unsubscribeUrl;
    public $trackingPixel;

    /**
     * Create a new message instance.
     */
    public function __construct(Suporte $suporte, string $message)
    {
        $this->suporte = $suporte;
        $this->message = $message;
        $this->unsubscribeUrl = $this->suporte->user ? route('unsubscribe', [
            'userId' => $this->suporte->user->id,
            'token' => hash('sha256', $this->suporte->user->email . env('APP_KEY'))
        ]) : '#';
        $this->trackingPixel = $this->buildTrackingPixel();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nova mensagem do suporte - Chamado #' . $this->suporte->id,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.support_message'
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
