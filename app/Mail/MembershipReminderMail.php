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

    public string $unsubscribeUrl;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        $this->unsubscribeUrl = '#'; // No specific user
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vamos finalizar a compra do seu VIP?',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.membership_reminder',
            text: 'emails.membership_reminder_plain',
            data: [
                'trackingPixel' => $this->buildTrackingPixel(),
                'unsubscribeUrl' => $this->unsubscribeUrl,
            ]
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
