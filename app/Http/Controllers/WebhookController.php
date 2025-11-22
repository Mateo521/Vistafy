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
        ]);

        $data = $request->all();

        Log::info(' Webhook recibido de Mercado Pago', array_merge($data, [
            'data_id' => $data['data']['id'] ?? null,
        ]));

        // Mercado Pago puede enviar diferentes tipos de notificaciones
        $topic = $data['topic'] ?? $data['type'] ?? null;

        if (!$topic) {
            Log::warning(' Webhook sin topic/type');
            return response()->json(['status' => 'ignored'], 200);
        }

        // Solo procesar notificaciones de pagos
        if ($topic === 'payment') {
            $paymentId = $data['data']['id'] ?? null;

            if (!$paymentId) {
                Log::warning(' Webhook de payment sin ID');
                return response()->json(['status' => 'ignored'], 200);
            }

            return $this->processPayment($paymentId);
        }

        // Procesar merchant_order si es necesario
        if ($topic === 'merchant_order') {
            $merchantOrderId = $data['id'] ?? null;

            if (!$merchantOrderId) {
                Log::warning(' Webhook de merchant_order sin ID');
                return response()->json(['status' => 'ignored'], 200);
            }

            Log::info(' Merchant order obtenida', ['id' => $merchantOrderId]);

            // Obtener la orden y extraer el payment_id
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . config('services.mercadopago.access_token'),
                ])->get("https://api.mercadopago.com/merchant_orders/{$merchantOrderId}");

                if ($response->successful()) {
                    $merchantOrder = $response->json();

                    // Extraer payment_id de la merchant order
                    $payments = $merchantOrder['payments'] ?? [];

                    if (!empty($payments)) {
                        $paymentId = $payments[0]['id'] ?? null;

                        if ($paymentId) {
                            return $this->processPayment($paymentId);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error(' Error procesando merchant_order', [
                    'merchant_order_id' => $merchantOrderId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info(' Webhook ignorado', ['topic' => $topic]);
        return response()->json(['status' => 'ignored'], 200);
    }

    private function processPayment($paymentId)
    {
        Log::info(' Procesando payment', ['payment_id' => $paymentId]);

        //  Intentar hasta 3 veces con delay
        $maxAttempts = 3;
        $delaySeconds = 2;
        $payment = null;
        $accessToken = env('MERCADOPAGO_ACCESS_TOKEN');

        if (!$accessToken) {
            Log::error(' Access Token no configurado en .env');
            return response()->json(['error' => 'Access token not configured'], 500);
        }
        Log::info('🔑 [WebhookController] Usando Access Token', [
            'token_preview' => substr($accessToken, 0, 30) . '...',
            'token_length' => strlen($accessToken),
        ]);

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                Log::info(" Intento {$attempt}/{$maxAttempts} de obtener payment", [
                    'payment_id' => $paymentId,
                ]);

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                ])->timeout(10)->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

                if ($response->successful()) {
                    $payment = $response->json();

                    Log::info(' Payment obtenido exitosamente', [
                        'payment_id' => $paymentId,
                        'attempt' => $attempt,
                        'status' => $payment['status'],
                    ]);

                    break; //  Encontrado, salir del loop
                }

                if ($response->status() === 404) {
                    Log::warning(" Payment no encontrado (intento {$attempt}/{$maxAttempts})", [
                        'payment_id' => $paymentId,
                    ]);

                    // Si no es el último intento, esperar
                    if ($attempt < $maxAttempts) {
                        Log::info("⏸️ Esperando {$delaySeconds} segundos antes de reintentar...");
                        sleep($delaySeconds);
                        continue;
                    } else {
                        Log::error(' Payment no encontrado después de todos los intentos', [
                            'payment_id' => $paymentId,
                            'attempts' => $maxAttempts,
                        ]);

                        return response()->json(['error' => 'Payment not found after retries'], 404);
                    }
                }

                // Otro error que no es 404
                Log::error(' Error al obtener información de pago', [
                    'payment_id' => $paymentId,
                    'attempt' => $attempt,
                    'status_code' => $response->status(),
                    'response_body' => $response->body(),
                ]);

                return response()->json(['error' => 'Payment fetch failed'], 500);

            } catch (\Exception $e) {
                Log::error(' Excepción al obtener payment', [
                    'payment_id' => $paymentId,
                    'attempt' => $attempt,
                    'exception' => $e->getMessage(),
                ]);

                if ($attempt === $maxAttempts) {
                    return response()->json(['error' => 'Exception occurred'], 500);
                }

                sleep($delaySeconds);
            }
        }

        // Si no se obtuvo el payment después de todos los intentos
        if (!$payment) {
            Log::error(' No se pudo obtener el payment', ['payment_id' => $paymentId]);
            return response()->json(['error' => 'Payment fetch failed'], 500);
        }

        //  Continuar con el procesamiento del payment
        Log::info(' Payment obtenido', [
            'id' => $payment['id'],
            'status' => $payment['status'],
            'status_detail' => $payment['status_detail'] ?? null,
            'external_reference' => $payment['external_reference'] ?? null,
        ]);

        $purchaseId = $payment['external_reference'] ?? null;

        if (!$purchaseId) {
            Log::warning(' Payment sin external_reference', [
                'payment_id' => $paymentId,
            ]);
            return response()->json(['status' => 'no_reference'], 200);
        }

        $purchase = Purchase::find($purchaseId);

        if (!$purchase) {
            Log::error(' Purchase no encontrada', [
                'purchase_id' => $purchaseId,
                'payment_id' => $paymentId,
            ]);
            return response()->json(['error' => 'Purchase not found'], 404);
        }

        $purchase->mp_payment_id = $paymentId;

        switch ($payment['status']) {
            case 'approved':
                $purchase->status = 'approved'; //  Cambio aquí
                Log::info(' Pago completado', [
                    'purchase_id' => $purchase->id,
                    'payment_id' => $paymentId,
                ]);
                break;

            case 'pending':
            case 'in_process':
                $purchase->status = $payment['status']; //  Usar el status real
                Log::info(' Pago pendiente', [
                    'purchase_id' => $purchase->id,
                    'payment_id' => $paymentId,
                ]);
                break;

            case 'rejected':
                $purchase->status = 'rejected'; //  Cambio aquí
                Log::info(' Pago rechazado', [
                    'purchase_id' => $purchase->id,
                    'payment_id' => $paymentId,
                    'status_detail' => $payment['status_detail'] ?? 'unknown',
                ]);
                break;

            case 'cancelled':
                $purchase->status = 'cancelled'; //  Cambio aquí
                Log::info(' Pago cancelado', [
                    'purchase_id' => $purchase->id,
                    'payment_id' => $paymentId,
                ]);
                break;

            default:
                Log::warning(' Estado de pago desconocido', [
                    'purchase_id' => $purchase->id,
                    'payment_id' => $paymentId,
                    'status' => $payment['status'],
                ]);
        }




        $purchase->save();

        return response()->json(['status' => 'processed'], 200);
    }






}
