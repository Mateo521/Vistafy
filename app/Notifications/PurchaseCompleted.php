<?php

namespace App\Notifications;

use App\Models\Purchase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public $purchase;

    public function __construct(Purchase $purchase)
    {
        $this->purchase = $purchase->load('items.photo.event');
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(' ¡Tu compra ha sido completada! - EMPRESA Photography')
            ->view('emails.purchase-completed', [
                'purchase' => $this->purchase,
            ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'purchase_id' => $this->purchase->id,
            'total_amount' => $this->purchase->total_amount,
            'item_count' => $this->purchase->items->count(),
        ];
    }
}
