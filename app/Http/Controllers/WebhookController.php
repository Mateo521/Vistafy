<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use App\Notifications\PurchaseCompleted;

class WebhookController extends Controller
{
    public function mercadoPago(Request $request)
    {
        Log::info(' Webhook recibido raw', [
            'raw' => $request->getContent(),
        ]);

        $data = $request->all();

        // Si viene como query params
        if (empty($data) && $request->has('topic')) {
            $data = [
                'topic' => $request->query('topic'),
                'id' => $request->query('id'),
                'data' => ['id' => $request->query('id')]
            ];
        }

        $topic = $data['topic'] ?? $data['type'] ?? null;

        if (!$topic) {
            Log::warning(' Webhook sin topic/type');
            return response()->json(['status' => 'ignored'], 200);
        }

        // Procesar payment
        if ($topic === 'payment') {
            $paymentId = $data['data']['id'] ?? $data['id'] ?? null;

            if (!$paymentId) {
                return response()->json(['status' => 'ignored'], 200);
            }

            sleep(2); // Dar tiempo a MP a sincronizar sus servidores
            return $this->processMarketplacePayment($paymentId);
        }

        // Ignoramos merchant_order porque en Marketplace los pagos se validan directo por el payment_id
        if ($topic === 'merchant_order' || $topic === 'topic_merchant_order_wh') {
            return response()->json(['status' => 'ignored'], 200);
        }

        return response()->json(['status' => 'ignored'], 200);
    }

    /**
     * Procesa un pago de Marketplace
     */
    private function processMarketplacePayment($paymentId)
    {
        Log::info(' Procesando payment Marketplace', ['payment_id' => $paymentId]);

        // PASO CLAVE MARKETPLACE: Primero necesitamos saber DE QUIÉN es el pago
        // MP siempre manda el external_reference en el webhook secundario, pero a veces no en el inicial.
        // Como no sabemos el fotógrafo aún, tenemos que usar la "Master Key" (Tu Token) para buscar la info básica.
        
        $masterToken = config('services.mercadopago.access_token');
        $paymentInfo = $this->fetchPaymentData($paymentId, $masterToken);

        if (!$paymentInfo) {
            Log::error(' No se pudo obtener el payment con la Master Key', ['payment_id' => $paymentId]);
            return response()->json(['status' => 'payment_fetch_failed', 'will_retry' => true], 200);
        }

        $purchaseId = $paymentInfo['external_reference'] ?? null;

        if (!$purchaseId) {
            Log::warning(' Payment sin external_reference. Posiblemente no es de Vistafy.', ['payment_id' => $paymentId]);
            return response()->json(['status' => 'no_reference'], 200);
        }

        $purchase = Purchase::with('items.photo.photographer')->find($purchaseId);

        if (!$purchase) {
            Log::error(' Purchase no encontrada en BD', ['purchase_id' => $purchaseId]);
            return response()->json(['error' => 'Purchase not found'], 404);
        }

        if ($purchase->status === 'approved') {
            Log::info(' Compra ya estaba aprobada, saltando update', ['purchase_id' => $purchaseId]);
            return response()->json(['status' => 'already_approved'], 200);
        }

        // Mapear status
        $newStatus = match ($paymentInfo['status']) {
            'approved' => 'approved',
            'pending', 'in_process', 'in_mediation' => 'in_process',
            'rejected' => 'rejected',
            'cancelled' => 'cancelled',
            'refunded', 'charged_back' => 'refunded',
            default => 'pending',
        };

        $purchase->update([
            'mp_payment_id' => $paymentId,
            'mp_payment_status' => $paymentInfo['status'],  
            'status' => $newStatus,
        ]);

        // Si se aprueba ahora, creamos cuenta y mandamos mail
        if ($newStatus === 'approved') {
            $this->handleAccountCreation($purchase);
            $this->sendSuccessEmail($purchase);
            
            // Limpiar carrito si estaba logueado
            if ($purchase->user) {
                $purchase->user->cartItems()->delete();
            }
        }

        Log::info(' Compra actualizada vía Webhook', [
            'purchase_id' => $purchase->id,
            'status' => $newStatus,
        ]);

        return response()->json(['status' => 'processed'], 200);
    }

    /**
     * Busca los datos del pago en MP con reintentos
     */
    private function fetchPaymentData($paymentId, $accessToken)
    {
        $maxAttempts = 3;
        $delaySeconds = 2;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                ])->timeout(10)->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

                if ($response->successful()) {
                    return $response->json();
                }

                if ($response->status() === 404 && $attempt < $maxAttempts) {
                    sleep($delaySeconds);
                    continue;
                }

            } catch (\Exception $e) {
                if ($attempt === $maxAttempts) {
                    return null;
                }
                sleep($delaySeconds);
            }
        }
        return null;
    }

    /**
     * Crear cuenta automática para invitados
     */
    private function handleAccountCreation($purchase)
    {
        $pendingAccount = session('pending_account');

        if ($pendingAccount && $pendingAccount['purchase_id'] == $purchase->id) {
            $email = $pendingAccount['email'];
            
            if (!User::where('email', $email)->exists()) {
                $tempPassword = Str::random(12);

                $user = User::create([
                    'name' => explode('@', $email)[0],
                    'email' => $email,
                    'password' => Hash::make($tempPassword),
                    'role' => 'client',
                ]);

                $purchase->update([
                    'user_id' => $user->id,
                    'buyer_name' => $user->name,
                ]);

                try {
                    \Mail::send('emails.account-created', [
                        'email' => $email,
                        'password' => $tempPassword,
                    ], function ($message) use ($email) {
                        $message->to($email)->subject('Tu cuenta ha sido creada - Vistafy');
                    });
                } catch (\Exception $e) {
                    Log::error('Error enviando email de cuenta', ['error' => $e->getMessage()]);
                }
            }
            session()->forget('pending_account');
        }
    }

    /**
     * Enviar email de éxito
     */
    private function sendSuccessEmail($purchase)
    {
        $email = $purchase->user ? $purchase->user->email : $purchase->guest_email;
        if ($email) {
            try {
                Notification::route('mail', $email)->notify(new PurchaseCompleted($purchase));
            } catch (\Exception $e) {
                Log::error('Error enviando notificación de compra', ['error' => $e->getMessage()]);
            }
        }
    }
}