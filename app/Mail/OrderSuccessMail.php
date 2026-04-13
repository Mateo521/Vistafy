<?php

namespace App\Mail;

use App\Models\Purchase;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Definimos las propiedades públicas para que estén 
     * disponibles automáticamente en la vista Blade.
     */
    public function __construct(
        public Purchase $purchase,
        public ?string $temporaryPassword = null
    ) {}

    /**
     * El "Sobre" del mail: El asunto que verá el cliente.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Tu pago en f33 fue aprobado!',
        );
    }

    /**
     * El "Cuerpo": Qué archivo HTML vamos a usar.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order-success',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}