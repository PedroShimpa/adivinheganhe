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

    public $subject;
    public $unsubscribeUrl;
    public $trackingPixel;

    public function __construct()
    {
        $this->subject = 'Alerta: Alto número de registros de usuários';
        $this->unsubscribeUrl = '#'; // Admin alert
        $this->trackingPixel = $this->buildTrackingPixel();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.high_registration_alert'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
