<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Purchase;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class PaymentController extends Controller
{
    protected MercadoPagoService $mercadoPagoService;

    public function __construct(MercadoPagoService $mercadoPagoService)
    {
        $this->mercadoPagoService = $mercadoPagoService;
    }

    /**
     * Iniciar proceso de compra
     */
    public function initiatePurchase(Request $request, Photo $photo)
    {
        // Validar email si es invitado
        if (!auth()->check()) {
            $request->validate([
                'email' => 'required|email',
                'create_account' => 'boolean',
            ]);
        }

        try {
            Log::info(' Iniciando compra', [
                'photo_id' => $photo->id,
                'user_id' => auth()->id(),
                'is_guest' => !auth()->check(),
                'email' => $request->email ?? auth()->user()?->email,
            ]);

            // Obtener email
            $email = auth()->check()
                ? auth()->user()->email
                : $request->email;

            // Verificar si la foto está disponible
            if (!$photo->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta foto no está disponible para la venta',
                ], 400);
            }

            // Crear preferencia de pago
            $preference = $this->mercadoPagoService->createPhotoPreference($photo, $email);

            // Si el invitado quiere crear cuenta, guardar su intención
            if (!auth()->check() && $request->create_account) {
                // Guardamos esto en la compra para procesarlo después
                Purchase::where('id', $preference['purchase_id'])
                    ->update([
                        'metadata' => [
                            'create_account' => true,
                            'guest_email' => $email,
                        ]
                    ]);
            }

            return response()->json($preference);

        } catch (\Exception $e) {
            Log::error(' Error al iniciar compra', [
                'error' => $e->getMessage(),
                'photo_id' => $photo->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la compra: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Página de éxito
     */
    public function success(Request $request)
    {
        $purchaseId = $request->query('purchase_id')
            ?? $request->query('external_reference');

        if (!$purchaseId) {
            Log::error(' Purchase ID no encontrado en URL', [
                'all_params' => $request->all(),
            ]);
            return redirect()->route('home')->with('error', 'ID de compra no encontrado');
        }

        $purchase = Purchase::with('photo')->find($purchaseId);

        if (!$purchase) {
            Log::error(' Purchase no encontrado en DB', [
                'purchase_id' => $purchaseId,
            ]);
            return redirect()->route('home')->with('error', 'Compra no encontrada');
        }

        //  Obtener todos los parámetros de la URL
        $merchantOrderId = $request->query('merchant_order_id');
        $paymentId = $request->query('payment_id') ?? $request->query('collection_id');
        $paymentStatus = $request->query('status') ?? $request->query('collection_status');
        $preferenceId = $request->query('preference_id');

        Log::info('🎯 Usuario accedió a página de éxito', [
            'purchase_id' => $purchase->id,
            'status' => $purchase->status,
            'merchant_order_id' => $merchantOrderId,
            'payment_id_from_url' => $paymentId,
            'payment_status_from_url' => $paymentStatus,
            'preference_id' => $preferenceId,
            'all_params' => $request->all(),
        ]);

        //  Si ya está aprobado, retornar directamente
        if ($purchase->status === 'approved') {
            Log::info(' Purchase ya está approved', ['purchase_id' => $purchase->id]);
            return Inertia::render('Payment/Success', [
                'purchase' => $purchase->load('photo'),
            ]);
        }

        //  MEJORADO: Procesar desde merchant_order con REINTENTOS
        if ($merchantOrderId && $paymentStatus === 'approved') {
            Log::info(' Procesando desde merchant_order', [
                'merchant_order_id' => $merchantOrderId,
                'status_from_url' => $paymentStatus,
            ]);

            try {
                $token = config('services.mercadopago.access_token');

                if (!$token) {
                    Log::error(' Access token no configurado');
                    return Inertia::render('Payment/Success', [
                        'purchase' => $purchase->load('photo'),
                    ]);
                }

                //  REINTENTAR hasta 5 veces con delay de 2 segundos
                $maxAttempts = 5;
                $delaySeconds = 2;
                $paymentFound = false;

                for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                    Log::info(" Intento {$attempt}/{$maxAttempts} de obtener merchant_order con payments");

                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                    ])->timeout(10)->get("https://api.mercadopago.com/merchant_orders/{$merchantOrderId}");

                    if ($response->successful()) {
                        $merchantOrder = $response->json();
                        $payments = $merchantOrder['payments'] ?? [];

                        Log::info(' Merchant order obtenido', [
                            'id' => $merchantOrder['id'],
                            'status' => $merchantOrder['status'] ?? 'N/A',
                            'payments_count' => count($payments),
                            'attempt' => $attempt,
                        ]);

                        //  Si encontramos payments, procesar
                        if (!empty($payments)) {
                            $payment = $payments[0];

                            Log::info(' Payment encontrado en merchant_order', [
                                'payment_id' => $payment['id'],
                                'status' => $payment['status'] ?? 'N/A',
                                'transaction_amount' => $payment['transaction_amount'] ?? 0,
                            ]);

                            //  Actualizar purchase
                            if (isset($payment['status']) && $payment['status'] === 'approved') {
                                $purchase->update([
                                    'mp_payment_id' => $payment['id'],
                                    'mp_merchant_order_id' => $merchantOrderId,
                                    'status' => 'approved',
                                    'payment_details' => [
                                        'payment_method' => $payment['payment_type_id'] ?? 'account_money',
                                        'transaction_amount' => $payment['transaction_amount'] ?? $purchase->amount,
                                        'status' => $payment['status'],
                                        'date_approved' => $payment['date_approved'] ?? now()->toIso8601String(),
                                    ],
                                ]);

                                Log::info(' Purchase actualizado desde merchant_order', [
                                    'purchase_id' => $purchase->id,
                                    'new_status' => 'approved',
                                    'mp_payment_id' => $payment['id'],
                                ]);

                                $paymentFound = true;

                                //  Recargar purchase
                                $purchase = $purchase->fresh(['photo']);
                                break; //  Salir del loop
                            }
                        } else {
                            //  Payments vacío, esperar y reintentar
                            if ($attempt < $maxAttempts) {
                                Log::info(" Payments vacío, esperando {$delaySeconds}s... (intento {$attempt})");
                                sleep($delaySeconds);
                            } else {
                                Log::warning(' Payments vacío después de todos los intentos', [
                                    'merchant_order_id' => $merchantOrderId,
                                    'attempts' => $maxAttempts,
                                ]);
                            }
                        }
                    } else {
                        Log::error(' Error al obtener merchant_order', [
                            'status_code' => $response->status(),
                            'body' => $response->body(),
                            'attempt' => $attempt,
                        ]);

                        if ($attempt < $maxAttempts) {
                            sleep($delaySeconds);
                        }
                    }
                }

                //  Si no encontró el payment después de todos los intentos, intentar consultar directamente
                if (!$paymentFound && $paymentId) {
                    Log::info(' Intentando obtener payment directamente por ID', [
                        'payment_id' => $paymentId,
                    ]);

                    for ($attempt = 1; $attempt <= 3; $attempt++) {
                        $paymentResponse = Http::withHeaders([
                            'Authorization' => 'Bearer ' . $token,
                        ])->timeout(10)->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

                        if ($paymentResponse->successful()) {
                            $payment = $paymentResponse->json();

                            Log::info(' Payment obtenido directamente', [
                                'payment_id' => $payment['id'],
                                'status' => $payment['status'],
                            ]);

                            if ($payment['status'] === 'approved') {
                                $purchase->update([
                                    'mp_payment_id' => $payment['id'],
                                    'mp_merchant_order_id' => $merchantOrderId,
                                    'status' => 'approved',
                                    'payment_details' => [
                                        'payment_method' => $payment['payment_method_id'] ?? 'account_money',
                                        'transaction_amount' => $payment['transaction_amount'] ?? $purchase->amount,
                                        'status' => $payment['status'],
                                    ],
                                ]);

                                Log::info(' Purchase actualizado desde payment directo', [
                                    'purchase_id' => $purchase->id,
                                ]);

                                $purchase = $purchase->fresh(['photo']);
                                break;
                            }
                        } else if ($paymentResponse->status() === 404 && $attempt < 3) {
                            Log::warning(" Payment no encontrado, esperando {$delaySeconds}s... (intento {$attempt})");
                            sleep($delaySeconds);
                        }
                    }
                }

            } catch (\Exception $e) {
                Log::error(' Exception obteniendo merchant_order', [
                    'merchant_order_id' => $merchantOrderId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        //  FALLBACK: Si llegamos aquí y el status de URL dice "approved", confiar en eso
        if ($purchase->status !== 'approved' && $paymentStatus === 'approved' && $paymentId) {
            Log::warning(' API de MP no disponible después de todos los intentos, usando URL como fallback', [
                'purchase_id' => $purchase->id,
                'payment_id' => $paymentId,
                'status_from_url' => $paymentStatus,
            ]);

            $purchase->update([
                'mp_payment_id' => $paymentId,
                'mp_merchant_order_id' => $merchantOrderId,
                'status' => 'approved',
                'payment_details' => [
                    'payment_method' => $request->query('payment_type') ?? 'account_money',
                    'transaction_amount' => $purchase->amount,
                    'status' => 'approved',
                    'status_detail' => 'accredited_via_url_fallback',
                    'date_approved' => now()->toIso8601String(),
                    'source' => 'url_fallback_mp_api_unavailable',
                    'fallback_reason' => 'MP API did not return payment after multiple attempts',
                ],
            ]);

            Log::info(' Purchase actualizado via URL fallback', [
                'purchase_id' => $purchase->id,
                'payment_id' => $paymentId,
            ]);

            // Recargar purchase
            $purchase = $purchase->fresh(['photo']);
        }

        return Inertia::render('Payment/Success', [
            'purchase' => $purchase->load('photo'),
        ]);
    }





    /**
     * Página de fallo
     */
    public function failure(Request $request)
    {
        $purchaseId = $request->query('purchase_id');

        //  Cargar la foto con la relación
        $purchase = $purchaseId
            ? Purchase::with(['photo'])->find($purchaseId)
            : null;

        Log::info(' Usuario en página de fallo', [
            'purchase_id' => $purchaseId,
            'status' => $purchase?->status,
        ]);

        return Inertia::render('Payment/Failure', [
            'purchase' => $purchase,
        ]);
    }

    /**
     * Página de pendiente
     */
    public function pending(Request $request)
    {
        $purchaseId = $request->query('purchase_id');

        //  Cargar la foto con la relación
        $purchase = $purchaseId
            ? Purchase::with(['photo'])->find($purchaseId)
            : null;

        Log::info(' Usuario en página de pendiente', [
            'purchase_id' => $purchaseId,
            'status' => $purchase?->status,
        ]);

        return Inertia::render('Payment/Pending', [
            'purchase' => $purchase,
        ]);
    }

    /**
     * Descargar foto con token
     */
    public function download($token)
    {
        $purchase = Purchase::where('download_token', $token)
            ->where('status', 'approved')
            ->with('photo')
            ->first();

        if (!$purchase) {
            abort(404, 'Token inválido o compra no encontrada');
        }

        $photo = $purchase->photo;

        if (!$photo || !file_exists(storage_path('app/public/' . $photo->path))) {
            abort(404, 'Foto no encontrada');
        }

        // Incrementar contador de descargas
        $photo->increment('downloads');

        Log::info(' Descarga de foto', [
            'purchase_id' => $purchase->id,
            'photo_id' => $photo->id,
            'token' => $token,
        ]);

        return response()->download(
            storage_path('app/public/' . $photo->path),
            "vistafy_photo_{$photo->unique_id}.jpg"
        );
    }
}
