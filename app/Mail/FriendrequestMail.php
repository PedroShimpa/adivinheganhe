<?php

namespace App\Mail;

use App\Mail\Traits\Trackable;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class FriendrequestMail extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels, Trackable;
    
    public  string $fromUser;
    public  string $toUser;

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
                'friendRequestRoute' => route('users.friend_requests'),
                'trackingPixel' => $this->buildTrackingPixel(),
            ]
        );
    }
}
