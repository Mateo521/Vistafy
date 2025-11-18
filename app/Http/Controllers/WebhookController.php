<?php

namespace App\Http\Controllers;

use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected MercadoPagoService $mercadoPago;

    public function __construct(MercadoPagoService $mercadoPago)
    {
        $this->mercadoPago = $mercadoPago;
    }

    /**
     * Webhook de Mercado Pago
     */
    public function mercadoPago(Request $request)
    {
        Log::info('Webhook recibido de Mercado Pago', $request->all());

        try {
            $this->mercadoPago->processWebhookNotification($request->all());

            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            Log::error('Error procesando webhook de Mercado Pago', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }
}
