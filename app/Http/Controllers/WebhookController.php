<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected MercadoPagoService $mercadoPagoService;

    public function __construct(MercadoPagoService $mercadoPagoService)
    {
        $this->mercadoPagoService = $mercadoPagoService;
    }

    public function mercadoPago(Request $request)
    {
        Log::info('🔔 Webhook recibido raw', ['raw' => file_get_contents('php://input')]);

        $this->mercadoPagoService->processWebhookNotification($request->all());

        return response()->json(['status' => 'ok'], 200);
    }


    /**
     * Mapear estados de Mercado Pago a estados internos
     */
    protected function mapMercadoPagoStatus(string $mpStatus): string
    {
        return match ($mpStatus) {
            'approved' => 'approved',
            'pending' => 'pending',
            'in_process' => 'pending',
            'rejected' => 'rejected',
            'cancelled' => 'cancelled',
            'refunded' => 'refunded',
            'charged_back' => 'refunded',
            default => 'pending',
        };
    }
}
