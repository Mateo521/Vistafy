<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $event;
    public $applicant;

    public function __construct($event, $applicant)
    {
        $this->event = $event;
        $this->applicant = $applicant;
    }

    public function build()
    {
        return $this->subject('¡Solicitud aprobada! - ' . $this->event->title)
                    ->view('emails.application_accepted');
    }
}