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
            'mp_payment_id' => $purchase->mp_payment_id,
            'mp_preference_id' => $purchase->mp_preference_id,
        ]);

        //  Si ya está aprobado, retornar inmediatamente
        if ($purchase->status === 'approved') {
            Log::info(' Ya está aprobado', ['purchase_id' => $purchase->id]);
            return response()->json([
                'status' => 'approved',
                'download_url' => route('download.show', $purchase->download_token),
            ]);
        }

        //  Si está pending/in_process, consultar MP
        if (in_array($purchase->status, ['pending', 'in_process'])) {
            Log::info(' Status es pending, consultando MP...', [
                'purchase_id' => $purchase->id,
            ]);

            try {
                $token = config('services.mercadopago.access_token');

                //  OPCIÓN 1: Buscar por preference_id si existe
                if ($purchase->mp_preference_id) {
                    Log::info('🔎 Buscando por preference_id', [
                        'preference_id' => $purchase->mp_preference_id,
                    ]);

                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                    ])->timeout(10)->get("https://api.mercadopago.com/checkout/preferences/{$purchase->mp_preference_id}");

                    if ($response->successful()) {
                        $preference = $response->json();

                        // Buscar merchant_orders asociados a esta preferencia
                        $searchUrl = 'https://api.mercadopago.com/merchant_orders/search';
                        $searchParams = [
                            'preference_id' => $purchase->mp_preference_id,
                        ];

                        $searchResponse = Http::withHeaders([
                            'Authorization' => 'Bearer ' . $token,
                        ])->get($searchUrl, $searchParams);

                        if ($searchResponse->successful()) {
                            $searchData = $searchResponse->json();
                            $merchantOrders = $searchData['results'] ?? [];

                            Log::info(' Merchant orders encontrados', [
                                'count' => count($merchantOrders),
                            ]);

                            if (!empty($merchantOrders)) {
                                $merchantOrder = $merchantOrders[0];
                                $payments = $merchantOrder['payments'] ?? [];

                                if (!empty($payments)) {
                                    $payment = $payments[0];

                                    Log::info(' Payment encontrado', [
                                        'payment_id' => $payment['id'],
                                        'status' => $payment['status'] ?? 'N/A',
                                    ]);

                                    $newStatus = match ($payment['status'] ?? 'pending') {
                                        'approved' => 'approved',
                                        'rejected' => 'rejected',
                                        'cancelled' => 'cancelled',
                                        default => 'pending',
                                    };

                                    $purchase->update([
                                        'mp_payment_id' => $payment['id'],
                                        'mp_merchant_order_id' => $merchantOrder['id'],
                                        'status' => $newStatus,
                                        'payment_details' => [
                                            'payment_method' => $payment['payment_type_id'] ?? 'account_money',
                                            'transaction_amount' => $payment['transaction_amount'] ?? $purchase->amount,
                                        ],
                                    ]);

                                    Log::info(' Purchase actualizado', [
                                        'purchase_id' => $purchase->id,
                                        'new_status' => $newStatus,
                                    ]);

                                    if ($newStatus === 'approved') {
                                        return response()->json([
                                            'status' => 'approved',
                                            'download_url' => route('download.show', $purchase->download_token),
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }

                //  OPCIÓN 2: Buscar por external_reference (fallback)
                $url = 'https://api.mercadopago.com/v1/payments/search';
                $params = [
                    'external_reference' => (string) $purchase->id,
                    'sort' => 'date_created',
                    'criteria' => 'desc',
                ];

                Log::info('🔎 Buscando por external_reference (fallback)', [
                    'params' => $params,
                ]);

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                ])->get($url, $params);

                if ($response->successful()) {
                    $responseData = $response->json();
                    $results = $responseData['results'] ?? [];

                    Log::info(' Resultados de búsqueda', [
                        'count' => count($results),
                    ]);

                    if (!empty($results)) {
                        $payment = $results[0];

                        $newStatus = match ($payment['status']) {
                            'approved' => 'approved',
                            'rejected' => 'rejected',
                            'cancelled' => 'cancelled',
                            default => 'pending',
                        };

                        $purchase->update([
                            'mp_payment_id' => $payment['id'],
                            'status' => $newStatus,
                        ]);

                        if ($newStatus === 'approved') {
                            return response()->json([
                                'status' => 'approved',
                                'download_url' => route('download.show', $purchase->download_token),
                            ]);
                        }
                    }
                }

            } catch (\Exception $e) {
                Log::error(' Exception al verificar payment', [
                    'purchase_id' => $purchase->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'status' => $purchase->status,
        ]);
    }



}
