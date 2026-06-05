<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\User;
use App\Services\MercadoPagoService;
use App\Notifications\PurchaseCompleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PaymentSimulationController extends Controller
{
    protected $mpService;

    public function __construct(MercadoPagoService $mpService)
    {
        $this->mpService = $mpService;
    }

   
    public function show(Purchase $purchase)
    {
        if (!config('services.mercadopago.simulation_mode')) {
            abort(404, 'Modo de simulación desactivado');
        }

        // Cargar relaciones necesarias
        $purchase->load('items.photo.event', 'items.photo.photographer');

        return Inertia::render('Payment/Simulate', [
            'purchase' => [
                'id' => $purchase->id,
                'buyer_email' => $purchase->buyer_email,
                'buyer_name' => $purchase->buyer_name,
                'guest_email' => $purchase->guest_email,
                'total_amount' => (float) $purchase->total_amount,
                'currency' => $purchase->currency,
                'status' => $purchase->status,
                'created_at' => $purchase->created_at->format('d/m/Y H:i'),
                'items' => $purchase->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'unit_price' => (float) $item->unit_price,
                        'photo' => [
                            'id' => $item->photo->id,
                            'unique_id' => $item->photo->unique_id,
                            'title' => $item->photo->title,
                            'thumbnail_url' => $item->photo->thumbnail_url,
                            'event' => $item->photo->event ? [
                                'id' => $item->photo->event->id,
                                'name' => $item->photo->event->name,
                            ] : null,
                            'photographer' => $item->photo->photographer ? [
                                'name' => $item->photo->photographer->name,
                            ] : null,
                        ],
                    ];
                }),
            ],
        ]);
    }

  
    public function process(Request $request, Purchase $purchase)
    {
        if (!config('services.mercadopago.simulation_mode')) {
            abort(404, 'Modo de simulación desactivado');
        }

        $request->validate([
            'status' => 'required|in:approved,rejected,pending',
        ]);

        $simulatedStatus = $request->status;

        try {
          
            $purchase->update([
                'mp_payment_id' => 'SIM-' . time(),
                'mp_payment_status' => $simulatedStatus,
                'status' => $simulatedStatus,
            ]);

            Log::info(' Pago simulado', [
                'purchase_id' => $purchase->id,
                'status' => $simulatedStatus,
                'total' => $purchase->total_amount,
            ]);

          
            if ($simulatedStatus === 'approved') {
               
                $this->handleAccountCreation($purchase);

               
                $this->sendPurchaseNotification($purchase);

              
                if ($purchase->user) {
                    $purchase->user->cartItems()->delete();
                    Log::info(' Carrito limpiado', ['user_id' => $purchase->user->id]);
                }
            }

         
            switch ($simulatedStatus) {
                case 'approved':
                    return redirect()
                        ->route('payment.success', ['purchase_id' => $purchase->id])
                        ->with('success', '¡Pago simulado aprobado exitosamente!');

                case 'rejected':
                    return redirect()
                        ->route('payment.failure', ['purchase_id' => $purchase->id])
                        ->with('error', 'Pago simulado rechazado');

                case 'pending':
                    return redirect()
                        ->route('payment.pending', ['purchase_id' => $purchase->id])
                        ->with('info', 'Pago simulado en estado pendiente');

                default:
                    return redirect()->route('gallery.index');
            }

        } catch (\Exception $e) {
            Log::error(' Error en simulación de pago', [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('gallery.index')
                ->with('error', 'Error al procesar la simulación: ' . $e->getMessage());
        }
    }

   
    protected function handleAccountCreation($purchase)
    {
        $pendingAccount = session('pending_account');

        if ($pendingAccount && $pendingAccount['purchase_id'] == $purchase->id) {
            $email = $pendingAccount['email'];
            
            // Verificar que no exista el usuario
            if (!User::where('email', $email)->exists()) {
                $temporaryPassword = Str::random(12);

                $user = User::create([
                    'name' => explode('@', $email)[0],
                    'email' => $email,
                    'password' => Hash::make($temporaryPassword),
                    'role' => 'customer',
                ]);

              
                $purchase->update([
                    'user_id' => $user->id,
                    'buyer_name' => $user->name,
                    'buyer_email' => $user->email,
                ]);
 
                try {
                    \Mail::send('emails.account-created', [
                        'email' => $email,
                        'password' => $temporaryPassword,
                    ], function ($message) use ($email) {
                        $message->to($email)
                                ->subject('Tu cuenta ha sido creada - f33 Lorem ipsum...');
                    });

                    Log::info(' Cuenta creada automáticamente', [
                        'user_id' => $user->id,
                        'purchase_id' => $purchase->id,
                        'email' => $email,
                    ]);
                } catch (\Exception $e) {
                    Log::error(' Error enviando email de cuenta creada', [
                        'error' => $e->getMessage(),
                        'email' => $email,
                    ]);
                }
            }

            session()->forget('pending_account');
        }
    }

 
    protected function sendPurchaseNotification($purchase)
    {
        try {
            $email = $purchase->user 
                ? $purchase->user->email 
                : $purchase->guest_email;

            if ($email) {
                Notification::route('mail', $email)
                    ->notify(new PurchaseCompleted($purchase));

                Log::info('📧 Notificación de compra enviada', [
                    'purchase_id' => $purchase->id,
                    'email' => $email,
                ]);
            }
        } catch (\Exception $e) {
            Log::error(' Error enviando notificación de compra', [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
