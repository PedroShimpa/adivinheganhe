<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class FriendrequestMail extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;

    public $subject;
    public  string $fromUser;
    public  string $toUser;
    public $unsubscribeUrl;

    public function __construct(string $fromUser, string $toUser)
    {
        $this->subject = 'Novo pedido de amizade de ' . $fromUser;
        $this->fromUser = $fromUser;
        $this->toUser = $toUser;
        $user = \App\Models\User::where('username', $this->toUser)->first();
        $this->unsubscribeUrl = $user ? route('unsubscribe', [
            'userId' => $user->id,
            'token' => hash('sha256', $user->email . env('APP_KEY'))
        ]) : '#';
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
            view: 'emails.friendrequest'
        );
    }
}
