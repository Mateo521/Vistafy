<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Photographer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $event;
    public $inviter;

    public function __construct(Event $event, Photographer $inviter)
    {
        $this->event = $event;
        $this->inviter = $inviter;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡' . $this->inviter->business_name . ' te invitó a cubrir un evento en F33',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.events.invitation',
        );
    }
}