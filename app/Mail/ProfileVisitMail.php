<?php

namespace App\Mail;

use App\Mail\Traits\Trackable;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProfileVisitMail extends Mailable
{
    use Queueable, SerializesModels, Trackable;

    public string $unsubscribeUrl;
    public $trackingPixel;

    public function __construct(protected string $username)
    {
        $user = \App\Models\User::where('username', $this->username)->first();
        $this->unsubscribeUrl = $user ? route('unsubscribe', [
            'userId' => $user->id,
            'token' => hash('sha256', $user->email . env('APP_KEY'))
        ]) : '#';
        $this->trackingPixel = $this->buildTrackingPixel();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->username . ' visitou seu perfil!',
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
