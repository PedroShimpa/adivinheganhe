<?php

namespace App\Mail;

use App\Mail\Traits\Trackable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HighRegistrationAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, Trackable;

    public function __construct()
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Alerta: Alto número de registros de usuários',
        );
    }

    public function content(): Content
    {
        // This is an admin alert, no specific user, so placeholder
        $unsubscribeUrl = '#';

        return new Content(
            view: 'emails.high_registration_alert',
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
