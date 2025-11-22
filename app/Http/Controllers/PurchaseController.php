<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PurchaseController extends Controller
{
    public function checkStatus(Purchase $purchase)
    {
        Log::info(' Verificando status de compra', [
            'purchase_id' => $purchase->id,
            'current_status' => $purchase->status,
            'mp_preference_id' => $purchase->mp_preference_id,
            'mp_payment_id' => $purchase->mp_payment_id,
        ]);

        // Si ya está aprobado, retornar
        if ($purchase->status === 'approved') {
            Log::info(' Ya está aprobado', ['purchase_id' => $purchase->id]);
            return response()->json([
                'status' => 'approved',
                'download_url' => route('download.show', $purchase->download_token),
            ]);
        }

        // Si está pending, consultar MP
        if (in_array($purchase->status, ['pending', 'in_process'])) {
            Log::info(' Status es pending/in_process, consultando MP...', [
                'purchase_id' => $purchase->id,
            ]);

            try {
                $token = config('services.mercadopago.access_token');

                Log::info('🔑 Usando token para búsqueda', [
                    'token_preview' => substr($token, 0, 30) . '...',
                ]);

                // Buscar payments por external_reference
                $url = 'https://api.mercadopago.com/v1/payments/search';
                $params = [
                    'external_reference' => $purchase->id,
                    'sort' => 'date_created',
                    'criteria' => 'desc',
                ];

                Log::info(' Haciendo búsqueda en MP', [
                    'url' => $url,
                    'params' => $params,
                ]);

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                ])->timeout(10)->get($url, $params);

                Log::info(' Respuesta de MP', [
                    'status_code' => $response->status(),
                    'successful' => $response->successful(),
                ]);

                if ($response->successful()) {
                    $responseData = $response->json();
                    $results = $responseData['results'] ?? [];

                    Log::info(' Resultados de búsqueda', [
                        'purchase_id' => $purchase->id,
                        'results_count' => count($results),
                        'paging' => $responseData['paging'] ?? null,
                    ]);

                    if (!empty($results)) {
                        $payment = $results[0];

                        Log::info(' Payment encontrado por búsqueda', [
                            'payment_id' => $payment['id'],
                            'status' => $payment['status'],
                            'status_detail' => $payment['status_detail'] ?? null,
                            'external_reference' => $payment['external_reference'] ?? null,
                        ]);

                        $newStatus = match ($payment['status']) {
                            'approved' => 'approved',
                            'rejected' => 'rejected',
                            'cancelled' => 'cancelled',
                            'pending', 'in_process' => 'in_process',
                            default => 'pending',
                        };

                        $purchase->update([
                            'mp_payment_id' => $payment['id'],
                            'status' => $newStatus,
                            'payment_details' => [
                                'payment_method' => $payment['payment_method_id'] ?? null,
                                'status_detail' => $payment['status_detail'] ?? null,
                                'transaction_amount' => $payment['transaction_amount'] ?? null,
                            ],
                        ]);

                        Log::info('💾 Purchase actualizado', [
                            'purchase_id' => $purchase->id,
                            'new_status' => $newStatus,
                        ]);

                        if ($newStatus === 'approved') {
                            return response()->json([
                                'status' => 'approved',
                                'download_url' => route('download.show', $purchase->download_token),
                            ]);
                        }
                    } else {
                        Log::warning(' No se encontraron payments', [
                            'purchase_id' => $purchase->id,
                            'external_reference' => $purchase->id,
                        ]);
                    }
                } else {
                    Log::error(' Error en respuesta de MP', [
                        'status_code' => $response->status(),
                        'body' => $response->body(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error(' Exception al verificar payment', [
                    'purchase_id' => $purchase->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        Log::info(' Retornando status actual', [
            'purchase_id' => $purchase->id,
            'status' => $purchase->status,
        ]);

        return response()->json([
            'status' => $purchase->status,
        ]);
    }

}
