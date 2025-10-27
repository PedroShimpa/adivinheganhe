<?php

namespace App\Mail;

use App\Mail\Traits\Trackable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MembershipReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, Trackable;

    public $subject;
    public $unsubscribeUrl;
    public $trackingPixel;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        $this->subject = 'Vamos finalizar a compra do seu VIP?';
        $this->unsubscribeUrl = '#'; // No specific user
        $this->trackingPixel = $this->buildTrackingPixel();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.membership_reminder',
            text: 'emails.membership_reminder_plain'
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
