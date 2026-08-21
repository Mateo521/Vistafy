<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationRejectedMail extends Mailable
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
        return $this->subject('Actualización sobre tu solicitud - ' . $this->event->title)
                    ->view('emails.application_rejected');
    }
}