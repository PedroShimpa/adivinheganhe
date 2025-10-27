<?php

namespace App\Mail;

use App\Mail\Traits\Trackable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BanPlayerMail extends Mailable  implements ShouldQueue
{
    use Queueable, SerializesModels, Trackable;

    public string $unsubscribeUrl;
    public $trackingPixel;

    public function __construct()
    {
        $this->unsubscribeUrl = '#'; // Ban notification, no unsubscribe
        $this->trackingPixel = $this->buildTrackingPixel();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Aviso de Banimento',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.banned_forever'
        );
    }
}
