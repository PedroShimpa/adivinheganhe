<?php

namespace App\Mail;

use App\Mail\Traits\Trackable;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MembershipWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, Trackable;

    public $subject;
    public User $usuario;
    public $unsubscribeUrl;
    public $trackingPixel;

    public function __construct(User $usuario)
    {
        $this->subject = 'Bem-vindo aos VIPs!';
        $this->usuario = $usuario;
        $this->unsubscribeUrl = route('unsubscribe', [
            'userId' => $this->usuario->id,
            'token' => hash('sha256', $this->usuario->email . env('APP_KEY'))
        ]);
        $this->trackingPixel = $this->buildTrackingPixel();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.membership_welcome'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
