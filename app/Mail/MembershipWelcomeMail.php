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

    public User $usuario;

    public function __construct(User $usuario)
    {
        $this->usuario = $usuario;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bem-vindo aos VIPs!'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.membership_welcome',
            with: [
                'trackingPixel' => $this->buildTrackingPixel(),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
