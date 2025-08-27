<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FriendrequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $fromUser;
    public string $toUser;

    /**
     * Create a new message instance.
     */
    public function __construct(string $fromUser, string $toUser)
    {
        $this->fromUser = $fromUser;
        $this->toUser = $toUser;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Novo pedido de amizade de ' . $this->fromUser,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.friendrequest',
            with: [
                'fromUser' => $this->fromUser,
                'toUser' => $this->toUser,
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
