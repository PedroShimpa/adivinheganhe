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

    public $subject;
    public User $usuario;

    public $unsubscribeUrl;
    public $trackingPixel;

    public function __construct(User $usuario)
    {
        $this->subject = 'Novo usuário adquiriu membership VIP!';
        $this->usuario = $usuario;
        $this->unsubscribeUrl = '#'; // Admin notification, no unsubscribe
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
            view: 'emails.membership_purchase_admin'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
