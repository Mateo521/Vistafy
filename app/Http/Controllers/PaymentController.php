<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Services\MercadoPagoService;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PaymentController extends Controller
{
    protected $mpService;
    protected $cartService;

    public function __construct(MercadoPagoService $mpService, CartService $cartService)
    {
        $this->mpService = $mpService;
        $this->cartService = $cartService;
    }

    /**
     * 🛒 Iniciar compra desde el carrito
     */
    public function initiateCartPurchase(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Debes iniciar sesión para usar el carrito'
            ], 401);
        }

        $request->validate([
            'photo_ids' => 'required|array|min:1',
            'photo_ids.*' => 'exists:photos,id',
        ]);

        try {
            $user = Auth::user();
            $photos = Photo::whereIn('id', $request->photo_ids)->get();

            if ($photos->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron fotos válidas'
                ], 400);
            }

            // Crear la compra
            $purchase = Purchase::create([
                'user_id' => $user->id,
                'buyer_email' => $user->email,
                'buyer_name' => $user->name,
                'total_amount' => $photos->sum('price'),
                'currency' => 'ARS',
                'status' => 'pending',
                'order_token' => Str::random(64),
            ]);

            // Crear items de la compra
            foreach ($photos as $photo) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'photo_id' => $photo->id,
                    'unit_price' => $photo->price,
                ]);
            }

            // 🔥 SIMULACIÓN: Aprobar compra automáticamente en desarrollo
            if ($request->input('simulate_payment') && config('app.env') === 'local') {
                $purchase->update([
                    'status' => 'approved',
                    'mp_payment_id' => 'SIM-' . time(),
                    'mp_payment_status' => 'approved',
                ]);

                Log::info(' Compra del carrito simulada aprobada', [
                    'purchase_id' => $purchase->id,
                    'photo_count' => $photos->count(),
                ]);

                // Vaciar el carrito
                $this->cartService->clear();

                return response()->json([
                    'success' => true,
                    'simulated' => true,
                    'purchase_id' => $purchase->id,
                    'redirect_url' => route('payment.success', ['purchase_id' => $purchase->id])
                ]);
            }

            // 🌐 Crear preferencia en Mercado Pago usando el método del carrito
            $preferenceResult = $this->mpService->createCartPreference($photos, $user->email, $purchase);

            if (!$preferenceResult['success']) {
                throw new \Exception('Error al crear preferencia de Mercado Pago');
            }

            // Vaciar el carrito después de crear la compra exitosamente
            $this->cartService->clear();

            Log::info('🛒 Compra desde carrito iniciada', [
                'purchase_id' => $purchase->id,
                'photo_count' => $photos->count(),
                'total' => $purchase->total_amount,
            ]);

            return response()->json([
                'success' => true,
                'purchase_id' => $purchase->id,
                'sandbox_init_point' => $preferenceResult['sandbox_init_point'],
                'simulation_mode' => $preferenceResult['simulation_mode'] ?? false,
            ]);

        } catch (\Exception $e) {
            Log::error(' Error en compra desde carrito', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la compra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📸 Compra individual (usar el método existente del service)
     */
    public function initiatePurchase(Request $request, Photo $photo)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        try {
            // 🔥 Usar el método existente del MercadoPagoService
            $result = $this->mpService->createPhotoPreference($photo, $validated['email']);

            if (!$result['success']) {
                throw new \Exception('Error al crear preferencia de pago');
            }

            // Si es simulación, marcar como aprobada
            if ($result['simulation_mode'] ?? false) {
                $purchase = Purchase::find($result['purchase_id']);
                $purchase->update([
                    'status' => 'approved',
                    'mp_payment_id' => 'SIM-' . time(),
                    'mp_payment_status' => 'approved',
                ]);

                Log::info(' Compra individual simulada aprobada', [
                    'purchase_id' => $purchase->id,
                    'photo_id' => $photo->id,
                ]);
            }

            Log::info('📸 Compra individual iniciada', [
                'purchase_id' => $result['purchase_id'],
                'photo_id' => $photo->id,
                'email' => $validated['email'],
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error(' Error en compra individual', [
                'error' => $e->getMessage(),
                'photo_id' => $photo->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la compra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Success page
     */
    public function success(Request $request)
    {
        $purchaseId = $request->query('purchase_id');

        if (!$purchaseId) {
            return redirect()->route('home')
                ->with('error', 'ID de compra inválido');
        }

        $purchase = Purchase::with('items.photo.event', 'items.photo.photographer')
            ->findOrFail($purchaseId);

        return Inertia::render('Payment/Success', [
            'purchase' => $purchase,
        ]);
    }

    /**
     * Failure page
     */
    public function failure(Request $request)
    {
        $purchaseId = $request->query('purchase_id');

        return Inertia::render('Payment/Failure', [
            'purchase_id' => $purchaseId,
            'message' => 'El pago fue rechazado o cancelado',
        ]);
    }

    /**
     * Pending page
     */
    public function pending(Request $request)
    {
        $purchaseId = $request->query('purchase_id');

        return Inertia::render('Payment/Pending', [
            'purchase_id' => $purchaseId,
            'message' => 'Tu pago está siendo procesado',
        ]);
    }

    /**
     * Webhook de Mercado Pago
     */
    public function webhook(Request $request)
    {
        Log::info('🔔 Webhook recibido', $request->all());

        try {
            $paymentId = $request->input('data.id');
            
            if (!$paymentId) {
                Log::warning('⚠️ Webhook sin payment ID');
                return response()->json(['status' => 'ok']);
            }

            $payment = $this->mpService->getPayment($paymentId);

            if (!$payment) {
                Log::error(' Payment no encontrado', ['payment_id' => $paymentId]);
                return response()->json(['status' => 'error']);
            }

            $purchase = Purchase::where('id', $payment->external_reference)
                ->orWhere('order_token', $payment->external_reference)
                ->first();

            if (!$purchase) {
                Log::error(' Purchase no encontrada', ['reference' => $payment->external_reference]);
                return response()->json(['status' => 'error']);
            }

            $purchase->update([
                'mp_payment_id' => $payment->id,
                'mp_payment_status' => $payment->status,
                'status' => $payment->status === 'approved' ? 'approved' : 'pending',
            ]);

            Log::info(' Purchase actualizada desde webhook', [
                'purchase_id' => $purchase->id,
                'status' => $payment->status,
            ]);

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            Log::error(' Error en webhook', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }
}
