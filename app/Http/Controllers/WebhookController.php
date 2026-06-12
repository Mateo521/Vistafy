<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\User;
use App\Notifications\PurchaseCompleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    public function mercadoPago(Request $request)
    {
        Log::info(' Webhook recibido raw', [
            'raw' => $request->getContent(),
        ]);

        $data = $request->all();

        if (empty($data) && $request->has('topic')) {
            $data = [
                'topic' => $request->query('topic'),
                'id' => $request->query('id'),
                'data' => ['id' => $request->query('id')],
            ];
        }

        $topic = $data['topic'] ?? $data['type'] ?? null;

        if (! $topic) {
            Log::warning(' Webhook sin topic/type');

            return response()->json(['status' => 'ignored'], 200);
        }

        // Procesar payment
        if ($topic === 'payment') {
            $paymentId = $data['data']['id'] ?? $data['id'] ?? null;

            if (! $paymentId) {
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

    private function processMarketplacePayment($paymentId)
    {
        $masterToken = config('services.mercadopago.access_token');
        $paymentInfo = $this->fetchPaymentData($paymentId, $masterToken);

        if (! $paymentInfo) {
            return response()->json(['status' => 'error'], 200);
        }

        $purchaseId = $paymentInfo['external_reference'] ?? null;
        $purchase = Purchase::with('items.photo.photographer')->find($purchaseId);

        if (! $purchase) {
            return response()->json(['status' => 'not_found'], 404);
        }

        if ($purchase->status === 'approved') {
            return response()->json(['status' => 'already_done'], 200);
        }

        $isApproved = ($paymentInfo['status'] === 'approved');

        $purchase->update([
            'mp_payment_id' => $paymentId,
            'mp_payment_status' => $paymentInfo['status'],
            'status' => $isApproved ? 'approved' : 'pending',
        ]);

        if ($isApproved) {

            $temporaryPassword = $this->handleAccountCreation($purchase);

            
            $emailToSend = $purchase->buyer_email;

            if ($emailToSend) {
                $this->sendSuccessEmail($purchase, $temporaryPassword);
                Log::info(' Notificación de éxito enviada a: '.$emailToSend);
            }

            if ($purchase->items && $purchase->items->count() > 0) {
                foreach ($purchase->items as $item) {
                    if ($item->photo) {
                        $item->photo->increment('downloads');
                        Log::info(' Descarga sumada a la foto ID: '.$item->photo->id);
                    }
                }
            }

            if ($purchase->user) {
                $cart = \App\Models\Cart::where('user_id', $purchase->user->id)->first();
                if ($cart) {
                    $cart->items()->delete();
                }
            }
        }

        return response()->json(['status' => 'processed'], 200);
    }

    private function fetchPaymentData($paymentId, $accessToken)
    {
        $maxAttempts = 3;
        $delaySeconds = 2;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.$accessToken,
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

    private function handleAccountCreation($purchase)
    {
        $wantsAccount = (! $purchase->user_id && is_null($purchase->guest_email));

        if ($wantsAccount && $purchase->buyer_email) {
            $user = User::where('email', $purchase->buyer_email)->first();

            if (! $user) {
                $password = Str::random(12);

                $user = User::create([
                    'name' => explode('@', $purchase->buyer_email)[0],
                    'email' => $purchase->buyer_email,
                    'password' => Hash::make($password),
                    'role' => 'client',
                ]);

                $purchase->update(['user_id' => $user->id]);

                return $password;
            } else {

                $purchase->update(['user_id' => $user->id]);
            }
        }

        return null;
    }

    private function sendSuccessEmail($purchase, $temporaryPassword = null)
    {
        $email = $purchase->buyer_email; 

        if ($email) {
            try {
                Notification::route('mail', $email)->notify(new \App\Notifications\PurchaseCompleted($purchase, $temporaryPassword));
            } catch (\Exception $e) {
                Log::error('Error enviando notificación de compra', ['error' => $e->getMessage()]);
            }
        } else {
            Log::error('Fallo silencioso: Se intentó enviar correo pero buyer_email está vacío', ['purchase_id' => $purchase->id]);
        }
    }
}
