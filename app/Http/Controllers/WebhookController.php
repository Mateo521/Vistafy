<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function mercadoPago(Request $request)
    {
        Log::info(' Webhook recibido raw', [
            'raw' => $request->getContent(),
            'headers' => $request->headers->all(),
        ]);

        $data = $request->all();

        // Si viene como query params (a veces MP lo envía así)
        if (empty($data) && $request->has('topic')) {
            $data = [
                'topic' => $request->query('topic'),
                'id' => $request->query('id'),
                'data' => ['id' => $request->query('id')]
            ];
        }

        Log::info(' Webhook recibido de Mercado Pago', $data);

        $topic = $data['topic'] ?? $data['type'] ?? null;

        if (!$topic) {
            Log::warning(' Webhook sin topic/type');
            return response()->json(['status' => 'ignored'], 200);
        }

        // Procesar payment directamente
        if ($topic === 'payment') {
            $paymentId = $data['data']['id'] ?? $data['id'] ?? null;

            if (!$paymentId) {
                Log::warning(' Webhook de payment sin ID');
                return response()->json(['status' => 'ignored'], 200);
            }

            //  Esperar 2 segundos antes de procesar (dar tiempo a MP)
            sleep(2);

            return $this->processPayment($paymentId);
        }


        // Procesar merchant_order
        if ($topic === 'merchant_order') {
            $merchantOrderId = $data['data']['id'] ?? $data['id'] ?? null;

            if (!$merchantOrderId) {
                Log::warning(' Webhook de merchant_order sin ID');
                return response()->json(['status' => 'ignored'], 200);
            }

            Log::info(' Procesando merchant_order', ['id' => $merchantOrderId]);

            try {
                $accessToken = config('services.mercadopago.access_token');

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                ])->timeout(10)->get("https://api.mercadopago.com/merchant_orders/{$merchantOrderId}");

                if ($response->successful()) {
                    $merchantOrder = $response->json();
                    $payments = $merchantOrder['payments'] ?? [];

                    Log::info(' Payments en merchant_order', [
                        'count' => count($payments),
                        'payments' => $payments,
                    ]);

                    if (!empty($payments)) {
                        $paymentId = $payments[0]['id'] ?? null;

                        if ($paymentId) {
                            Log::info(' Procesando payment desde merchant_order', ['payment_id' => $paymentId]);
                            return $this->processPayment($paymentId);
                        }
                    }

                    //  IMPORTANTE: Retornar success si no hay payments aún
                    return response()->json(['status' => 'processed'], 200);
                }

                Log::error(' Error al obtener merchant_order', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

            } catch (\Exception $e) {
                Log::error(' Exception procesando merchant_order', [
                    'error' => $e->getMessage(),
                ]);
            }

            //  Retornar 200 incluso si falla para que MP no reintente infinitamente
            return response()->json(['status' => 'error'], 200);
        }

        Log::info(' Tipo de notificación no manejada', ['topic' => $topic]);
        return response()->json(['status' => 'ignored'], 200);
    }

    private function processPayment($paymentId)
    {
        Log::info(' Procesando payment', ['payment_id' => $paymentId]);

        $accessToken = config('services.mercadopago.access_token');

        if (!$accessToken) {
            Log::error(' Access Token no configurado');
            return response()->json(['error' => 'Access token not configured'], 500);
        }

        //  AUMENTAR REINTENTOS: 5 intentos con 3 segundos de espera
        $maxAttempts = 5;
        $delaySeconds = 3;
        $payment = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                Log::info(" Intento {$attempt}/{$maxAttempts}", ['payment_id' => $paymentId]);

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                ])->timeout(10)->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

                if ($response->successful()) {
                    $payment = $response->json();
                    Log::info(' Payment obtenido', [
                        'payment_id' => $paymentId,
                        'status' => $payment['status'],
                        'status_detail' => $payment['status_detail'] ?? 'N/A',
                    ]);
                    break;
                }

                if ($response->status() === 404 && $attempt < $maxAttempts) {
                    Log::warning(" Payment no encontrado, esperando {$delaySeconds}s... (intento {$attempt}/{$maxAttempts})");
                    sleep($delaySeconds);
                    continue;
                }

                Log::error(' Error al obtener payment', [
                    'status_code' => $response->status(),
                    'body' => $response->body(),
                ]);

                //  NO RETORNAR ERROR 500, retornar 200 para que MP no reintente inmediatamente
                return response()->json(['status' => 'payment_fetch_failed', 'will_retry' => true], 200);

            } catch (\Exception $e) {
                Log::error(' Exception al obtener payment', [
                    'error' => $e->getMessage(),
                    'attempt' => $attempt,
                ]);

                if ($attempt === $maxAttempts) {
                    return response()->json(['status' => 'exception', 'will_retry' => true], 200);
                }

                sleep($delaySeconds);
            }
        }

        if (!$payment) {
            Log::error(' No se pudo obtener el payment después de todos los intentos', [
                'payment_id' => $paymentId,
            ]);
            //  Retornar 200 para que MP reintente más tarde
            return response()->json(['status' => 'payment_not_found_yet', 'will_retry' => true], 200);
        }

        // Procesar el payment
        $purchaseId = $payment['external_reference'] ?? null;

        if (!$purchaseId) {
            Log::warning(' Payment sin external_reference', ['payment_id' => $paymentId]);
            return response()->json(['status' => 'no_reference'], 200);
        }

        $purchase = Purchase::find($purchaseId);

        if (!$purchase) {
            Log::error(' Purchase no encontrada', ['purchase_id' => $purchaseId]);
            return response()->json(['error' => 'Purchase not found'], 404);
        }

        // Mapear status
        $newStatus = match ($payment['status']) {
            'approved' => 'approved',
            'pending', 'in_process', 'in_mediation' => 'in_process',
            'rejected' => 'rejected',
            'cancelled' => 'cancelled',
            'refunded', 'charged_back' => 'refunded',
            default => 'pending',
        };

        $purchase->update([
            'mp_payment_id' => $paymentId,
            'status' => $newStatus,
            'payment_details' => [
                'payment_method' => $payment['payment_method_id'] ?? null,
                'payment_type' => $payment['payment_type_id'] ?? null,
                'status' => $payment['status'] ?? null,
                'status_detail' => $payment['status_detail'] ?? null,
                'transaction_amount' => $payment['transaction_amount'] ?? null,
                'date_approved' => $payment['date_approved'] ?? null,
            ],
        ]);

        Log::info(' Compra actualizada', [
            'purchase_id' => $purchase->id,
            'status' => $newStatus,
            'payment_id' => $paymentId,
        ]);

        return response()->json(['status' => 'processed'], 200);
    }

}
