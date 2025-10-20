<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MembershipPurchaseAdminMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

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
        return new Content(
            view: 'emails.membership_purchase_admin'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
