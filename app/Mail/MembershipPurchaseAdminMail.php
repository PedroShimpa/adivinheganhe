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

class MembershipPurchaseAdminMail extends Mailable implements ShouldQueue
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
            subject: 'Novo usuário adquiriu membership VIP!'
        );
    }

    public function content(): Content
    {
        // This is an admin notification, no specific user, so placeholder
        $unsubscribeUrl = '#';

        return new Content(
            view: 'emails.membership_purchase_admin',
            with: [
                'trackingPixel' => $this->buildTrackingPixel(),
                'unsubscribeUrl' => $unsubscribeUrl,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
