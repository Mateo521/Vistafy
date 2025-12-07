<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentSimulationController extends Controller
{
    protected $mpService;

    public function __construct(MercadoPagoService $mpService)
    {
        $this->mpService = $mpService;
    }

    /**
     * Mostrar página de simulación de pago
     */
   public function show(Purchase $purchase)
{
    if (!config('services.mercadopago.simulation_mode')) {
        abort(404);
    }

    //  Asegúrate de cargar las relaciones Y castear correctamente
    $purchase->load('items.photo.event');

    return Inertia::render('Payment/Simulate', [
        'purchase' => [
            'id' => $purchase->id,
            'buyer_email' => $purchase->buyer_email,
            'buyer_name' => $purchase->buyer_name,
            'total_amount' => (float) $purchase->total_amount, //  Castear a float
            'currency' => $purchase->currency,
            'status' => $purchase->status,
            'created_at' => $purchase->created_at,
            'items' => $purchase->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'unit_price' => (float) $item->unit_price, //  Castear a float
                    'photo' => [
                        'id' => $item->photo->id,
                        'unique_id' => $item->photo->unique_id,
                        'event' => $item->photo->event ? [
                            'id' => $item->photo->event->id,
                            'name' => $item->photo->event->name,
                        ] : null,
                    ],
                ];
            }),
        ],
    ]);
}


    /**
     * Procesar simulación de pago
     */
    public function process(Request $request, Purchase $purchase)
    {
        if (!config('services.mercadopago.simulation_mode')) {
            abort(404);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected,pending',
        ]);

        $result = $this->mpService->processSimulatedPayment($purchase, $request->status);

        // Redirigir según el resultado
        switch ($request->status) {
            case 'approved':
                return redirect()->route('payment.success', ['purchase_id' => $purchase->id]);
            case 'rejected':
                return redirect()->route('payment.failure', ['purchase_id' => $purchase->id]);
            case 'pending':
                return redirect()->route('payment.pending', ['purchase_id' => $purchase->id]);
        }
    }
}
