<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProfileVisitMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $unsubscribeUrl;

    public function __construct(protected string $username)
    {
        $this->subject = $this->username . ' visitou seu perfil!';
        $user = \App\Models\User::where('username', $this->username)->first();
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
            view: 'emails.profile_visit'
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
