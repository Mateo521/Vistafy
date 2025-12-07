<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Photo;
use App\Services\MercadoPagoService;

use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Http;

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
        // 1. Definir reglas base
        $rules = [
            'create_account' => 'boolean',
        ];

        // 2. Si NO está logueado, el email es obligatorio
        if (!auth()->check()) {
            $rules['email'] = 'required|email';
        }

        // 3. Ejecutar validación (Esto lanza el 422 si falla)
        $validated = $request->validate($rules);

        try {
            // 4. Determinar el email final
            $email = auth()->check() ? auth()->user()->email : $validated['email'];

            Log::info(' Iniciando compra', [
                'user_id' => auth()->id(),
                'email' => $email,
                'photo_id' => $photo->id
            ]);

            // ... Llamada al servicio ...
            $response = $this->mercadoPagoService->createPhotoPreference($photo, $email);

            return response()->json($response);

        } catch (\Exception $e) {
            // ... (Manejo de errores igual que antes)
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

    //  Verificar que la compra tenga items
    if ($purchase->items->isEmpty()) {
        return redirect()->route('home')->with('error', 'Compra sin items');
    }

    return Inertia::render('Payment/Success', [
        'purchase' => $purchase,
        //  NO necesitas pasar 'photo' separado, ya está en purchase.items
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
