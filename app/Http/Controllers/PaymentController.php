<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Purchase;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        $purchaseId = $request->query('purchase_id');

        if (!$purchaseId) {
            return redirect()->route('home');
        }

        $purchase = Purchase::with(['photo', 'items'])->find($purchaseId);

        if (!$purchase) {
            Log::warning('⚠️ Compra no encontrada en success', [
                'purchase_id' => $purchaseId,
            ]);
            return redirect()->route('home');
        }

        Log::info('✅ Usuario en página de éxito', [
            'purchase_id' => $purchase->id,
            'status' => $purchase->status,
        ]);

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
