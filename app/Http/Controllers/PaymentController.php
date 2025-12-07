<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Photo;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    protected $mpService;

    public function __construct(MercadoPagoService $mpService)
    {
        $this->mpService = $mpService;
    }

    /**
     * Iniciar proceso de compra
     */
    public function initiatePurchase(Request $request, Photo $photo)
    {
        $request->validate([
            'email' => 'required_without:user_id|email',
            'create_account' => 'boolean',
        ]);

        try {
            $email = $request->email ?? auth()->user()->email;
            $result = $this->mpService->createPhotoPreference($photo, $email);

            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error('❌ [Payment] Error iniciando compra', [
                'photo_id' => $photo->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Pago exitoso
     */
/**
 * Pago exitoso
 */
public function success(Request $request)
{
    $purchaseId = $request->query('purchase_id');
    
    if (!$purchaseId) {
        return redirect()->route('home')->with('error', 'Compra no encontrada');
    }

    $purchase = Purchase::with('items.photo.event')->findOrFail($purchaseId);

    // 🔥 Verificar que la compra tenga items
    if ($purchase->items->isEmpty()) {
        return redirect()->route('home')->with('error', 'Compra sin items');
    }

    return Inertia::render('Payment/Success', [
        'purchase' => $purchase,
        // 🔥 NO necesitas pasar 'photo' separado, ya está en purchase.items
    ]);
}

    /**
     * Pago fallido
     */
    public function failure(Request $request)
    {
        $purchaseId = $request->query('purchase_id');
        
        if (!$purchaseId) {
            return redirect()->route('home')->with('error', 'Compra no encontrada');
        }

        $purchase = Purchase::with('items.photo')->findOrFail($purchaseId);

        return Inertia::render('Payment/Failure', [
            'purchase' => $purchase,
        ]);
    }

    /**
     * Pago pendiente
     */
    public function pending(Request $request)
    {
        $purchaseId = $request->query('purchase_id');
        
        if (!$purchaseId) {
            return redirect()->route('home')->with('error', 'Compra no encontrada');
        }

        $purchase = Purchase::with('items.photo')->findOrFail($purchaseId);

        return Inertia::render('Payment/Pending', [
            'purchase' => $purchase,
        ]);
    }

    /**
     * Descargar foto con token
     */
    public function download(string $token)
    {
        // Tu lógica de descarga existente...
    }
}
