<?php

namespace App\Notifications;

use App\Models\Purchase;
use App\Models\PurchasePayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public $purchase;
    public $password; 
    public $payment; 

    public function __construct(Purchase $purchase, $password = null, PurchasePayment $payment = null)
    {
        $this->purchase = $purchase->load('items.photo.event');
        $this->password = $password;
        
        if ($payment) {
            $this->payment = $payment->load('items.photo.event');
        }
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {

        $itemsToEmail = $this->payment ? $this->payment->items : $this->purchase->items;
        $amountToEmail = $this->payment ? $this->payment->amount : $this->purchase->total_amount;

        return (new MailMessage)
            ->subject('¡Tu compra ha sido completada! - F33')
            ->view('emails.purchase-completed', [
                'purchase' => $this->purchase,
                'items' => $itemsToEmail,          
                'amount' => $amountToEmail,       
                'password' => $this->password,  
            ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'purchase_id' => $this->purchase->id,
            'payment_id' => $this->payment ? $this->payment->id : null,
        ];
    }
}