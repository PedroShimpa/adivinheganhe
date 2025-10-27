<?php

namespace App\Mail;

use App\Models\Suporte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportResponseMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $subject;
    public Suporte $suporte;
    public $unsubscribeUrl;

    public function __construct(Suporte $suporte)
    {
        $this->subject = 'Atualização no seu chamado de suporte';
        $this->suporte = $suporte;
        $this->unsubscribeUrl = $this->suporte->user ? route('unsubscribe', [
            'userId' => $this->suporte->user->id,
            'token' => hash('sha256', $this->suporte->user->email . env('APP_KEY'))
        ]) : '#';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.support_response'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
