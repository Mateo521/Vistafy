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
            Log::info('🛒 Iniciando compra', [
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
            Log::error('❌ Error al iniciar compra', [
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
            Log::error('❌ Purchase ID no encontrado en URL', [
                'all_params' => $request->all(),
            ]);
            return redirect()->route('home')->with('error', 'ID de compra no encontrado');
        }

        $purchase = Purchase::with('photo')->find($purchaseId);

        if (!$purchase) {
            Log::error('❌ Purchase no encontrado en DB', [
                'purchase_id' => $purchaseId,
            ]);
            return redirect()->route('home')->with('error', 'Compra no encontrada');
        }

        $paymentId = $request->query('payment_id')
            ?? $request->query('collection_id');

        $paymentStatus = $request->query('status')
            ?? $request->query('collection_status');

        Log::info('✅ Usuario accedió a página de éxito', [
            'purchase_id' => $purchase->id,
            'status' => $purchase->status,
            'payment_id_from_url' => $paymentId,
            'payment_status_from_url' => $paymentStatus,
            'all_params' => $request->all(),
        ]);

        // Si ya está aprobado, retornar directamente
        if ($purchase->status === 'approved') {
            Log::info('✅ Purchase ya está approved', ['purchase_id' => $purchase->id]);
            return Inertia::render('Payment/Success', [
                'purchase' => $purchase,
            ]);
        }

        // Si viene payment_id en URL y status approved, consultar MP
        if ($paymentId && $paymentStatus === 'approved') {
            Log::info('💳 Consultando payment desde URL', [
                'payment_id' => $paymentId,
                'status_from_url' => $paymentStatus,
            ]);

            try {
                $token = config('services.mercadopago.access_token');

                if (!$token) {
                    Log::error('❌ Access token no configurado');
                    return Inertia::render('Payment/Success', [
                        'purchase' => $purchase,
                    ]);
                }

                Log::info('🔑 Usando token', [
                    'token_preview' => substr($token, 0, 30) . '...',
                ]);

                $url = "https://api.mercadopago.com/v1/payments/{$paymentId}";

                Log::info('📤 Haciendo request a MP', ['url' => $url]);

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                ])->timeout(10)->get($url);

                Log::info('📥 Respuesta de consulta directa', [
                    'status_code' => $response->status(),
                    'successful' => $response->successful(),
                ]);

                if ($response->successful()) {
                    $payment = $response->json();

                    Log::info('✅ Payment obtenido de URL', [
                        'payment_id' => $payment['id'],
                        'status' => $payment['status'],
                        'external_reference' => $payment['external_reference'] ?? null,
                    ]);

                    if ($payment['status'] === 'approved') {
                        $purchase->update([
                            'mp_payment_id' => $payment['id'],
                            'status' => 'approved',
                            'payment_details' => [
                                'payment_method' => $payment['payment_method_id'] ?? null,
                                'status_detail' => $payment['status_detail'] ?? null,
                                'transaction_amount' => $payment['transaction_amount'] ?? null,
                            ],
                        ]);

                        Log::info('💾 Purchase actualizado desde URL', [
                            'purchase_id' => $purchase->id,
                            'new_status' => 'approved',
                            'mp_payment_id' => $payment['id'],
                        ]);

                        // Recargar purchase
                        $purchase = $purchase->fresh();
                    }
                } else {
                    Log::error('❌ Error en respuesta de MP', [
                        'status_code' => $response->status(),
                        'body' => $response->body(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('❌ Exception obteniendo payment desde URL', [
                    'payment_id' => $paymentId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        } else {
            Log::warning('⚠️ No se puede consultar payment', [
                'payment_id' => $paymentId,
                'payment_status' => $paymentStatus,
                'reason' => !$paymentId ? 'payment_id missing' : 'status not approved',
            ]);
        }

        return Inertia::render('Payment/Success', [
            'purchase' => $purchase,
        ]);
    }


    /**
     * Página de fallo
     */
    public function failure(Request $request)
    {
        $purchaseId = $request->query('purchase_id');

        // 🔧 Cargar la foto con la relación
        $purchase = $purchaseId
            ? Purchase::with(['photo'])->find($purchaseId)
            : null;

        Log::info('❌ Usuario en página de fallo', [
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

        // 🔧 Cargar la foto con la relación
        $purchase = $purchaseId
            ? Purchase::with(['photo'])->find($purchaseId)
            : null;

        Log::info('⏳ Usuario en página de pendiente', [
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

        Log::info('📥 Descarga de foto', [
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
