<?php

namespace App\Mail;

use App\Models\FutureEvent;
use App\Models\Photographer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PhotographerApplicationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $event;
    public $applicant;  

    public function __construct(FutureEvent $event, Photographer $applicant)
    {
        $this->event = $event;
        $this->applicant = $applicant;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡' . $this->applicant->business_name . ' quiere cubrir tu evento',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.events.application',
        );
    }
}