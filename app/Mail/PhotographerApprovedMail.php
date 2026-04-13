<?php

namespace App\Mail;

use App\Models\Photographer;  
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PhotographerApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

   
    public function __construct(public Photographer $photographer)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Tu cuenta en f33 fue APROBADA!',
        );
    }

  public function content(): Content {
        return new Content(view: 'emails.photographer-approved');
    }

    public function attachments(): array
    {
        return [];
    }
}