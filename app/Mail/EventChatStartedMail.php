<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Photographer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventChatStartedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $event;
    public $sender;

    public function __construct(Event $event, Photographer $sender)
    {
        $this->event = $event;
        $this->sender = $sender;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva coordinación táctica: ' . $this->event->name,
        );
    }

    public function content(): Content
    {
    
        return new Content(
            view: 'emails.chat-started', 
        );
    }
}