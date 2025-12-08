<?php

namespace App\Services;

use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Purchase;
class MercadoPagoService
{
    protected $preferenceClient;
    protected $paymentClient;

    public function __construct()
    {
        $accessToken = config('services.mercadopago.access_token');

        if (!$accessToken) {
            throw new \Exception('Mercado Pago access token no configurado');
        }

        MercadoPagoConfig::setAccessToken($accessToken);
        MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);

        $this->preferenceClient = new PreferenceClient();
        $this->paymentClient = new PaymentClient(); //  Inicializar PaymentClient
    }

    /**
     * 🔍 Obtener información de un pago
     */
    public function getPayment($paymentId)
    {
        try {
            $payment = $this->paymentClient->get($paymentId);

            Log::info(' Payment obtenido', [
                'payment_id' => $paymentId,
                'status' => $payment->status,
                'external_reference' => $payment->external_reference,
            ]);

            return $payment;

        } catch (\Exception $e) {
            Log::error(' Error obteniendo payment', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * 🧪 Verificar si está en modo simulación
     */
    protected function isSimulationMode(): bool
    {
        return config('app.env') === 'local' &&
            config('services.mercadopago.simulation_mode', false);
    }

    /**
     *  Crear preferencia para foto individual
     */
    public function createPhotoPreference($photo, string $email): array
    {
        $buyerName = auth()->check() ? auth()->user()->name : 'Invitado';

        // 1. Crear compra en BD
        $purchase = \App\Models\Purchase::create([
            'user_id' => auth()->id(),
            'buyer_email' => $email,
            'buyer_name' => $buyerName,
            'total_amount' => $photo->price,
            'currency' => 'ARS',
            'status' => 'pending',
            'order_token' => Str::random(64),
        ]);

        // 2. Crear item
        $purchase->items()->create([
            'photo_id' => $photo->id,
            'unit_price' => $photo->price,
        ]);

        // 🧪 MODO SIMULACIÓN (LOCAL)
        if ($this->isSimulationMode()) {
            Log::info('🧪 [MP SIMULATION] Creando compra simulada', [
                'purchase_id' => $purchase->id,
                'photo_id' => $photo->id,
                'email' => $email,
                'amount' => $photo->price,
            ]);

            return [
                'success' => true,
                'purchase_id' => $purchase->id,
                'simulation_mode' => true,
                'sandbox_init_point' => route('payment.simulate', ['purchase' => $purchase->id]),
            ];
        }

        //  MODO REAL (PRODUCCIÓN / STAGING)
        $appUrl = config('app.url');
        $isLocal = app()->environment(['local', 'development']);

        $pictureUrl = $photo->thumbnail_url;
        if ($isLocal && (Str::contains($pictureUrl, 'localhost') || Str::contains($pictureUrl, '127.0.0.1'))) {
            $pictureUrl = null;
        }

        $preferenceData = [
            'items' => [
                [
                    'id' => (string) $photo->id,
                    'title' => "Foto Digital #{$photo->unique_id}",
                    'description' => $photo->event ? "Evento: {$photo->event->name}" : "Fotografía Profesional",
                    'picture_url' => $pictureUrl,
                    'category_id' => 'digital_goods',
                    'quantity' => 1,
                    'currency_id' => 'ARS',
                    'unit_price' => (float) $photo->price,
                ]
            ],

            'back_urls' => [
                'success' => route('payment.success', ['purchase_id' => $purchase->id]),
                'failure' => route('payment.failure', ['purchase_id' => $purchase->id]),
                'pending' => route('payment.pending', ['purchase_id' => $purchase->id]),
            ],

            'auto_return' => 'approved',
            'binary_mode' => true,
            'external_reference' => (string) $purchase->id,
            'notification_url' => $isLocal ? null : config('services.mercadopago.notification_url'),
            'statement_descriptor' => 'VISTAFY FOTOS',
        ];

        try {
            $preference = $this->preferenceClient->create($preferenceData);
            $purchase->update(['mp_preference_id' => $preference->id]);

            $isSandbox = config('services.mercadopago.test_mode');

            return [
                'success' => true,
                'purchase_id' => $purchase->id,
                'simulation_mode' => false,
                'sandbox_init_point' => $isSandbox ? $preference->sandbox_init_point : $preference->init_point,
            ];

        } catch (\Exception $e) {
            $apiError = $e->getMessage();
            $apiDetails = [];

            if (method_exists($e, 'getApiResponse') && $e->getApiResponse()) {
                $apiDetails = $e->getApiResponse()->getContent();
                Log::error('🛑 MERCADO PAGO API RESPONSE:', (array) $apiDetails);
            }

            Log::error(' [MP] Error creando preferencia', [
                'error' => $apiError,
                'details' => $apiDetails,
                'photo_id' => $photo->id,
                'email' => $email,
            ]);

            $purchase->update(['status' => 'failed']);
            throw new \Exception("Error al procesar la compra: " . $apiError);
        }
    }

    /**
     *  Crear preferencia para múltiples fotos (carrito)
     */
    public function createCartPreference($photos, string $email, $purchase): array
    {
        $buyerName = auth()->check() ? auth()->user()->name : 'Invitado';

        // 🧪 MODO SIMULACIÓN (LOCAL)
        if ($this->isSimulationMode()) {
            Log::info('🧪 [MP SIMULATION] Creando compra de carrito simulada', [
                'purchase_id' => $purchase->id,
                'photo_count' => $photos->count(),
                'email' => $email,
                'amount' => $purchase->total_amount,
            ]);

            return [
                'success' => true,
                'purchase_id' => $purchase->id,
                'simulation_mode' => true,
                'sandbox_init_point' => route('payment.simulate', ['purchase' => $purchase->id]),
            ];
        }

        //  MODO REAL (PRODUCCIÓN / STAGING)
        $appUrl = config('app.url');
        $isLocal = app()->environment(['local', 'development']);

        // Construir items del carrito
        $items = [];
        foreach ($photos as $photo) {
            $pictureUrl = $photo->thumbnail_url;
            if ($isLocal && (Str::contains($pictureUrl, 'localhost') || Str::contains($pictureUrl, '127.0.0.1'))) {
                $pictureUrl = null;
            }

            $items[] = [
                'id' => (string) $photo->id,
                'title' => "Foto Digital #{$photo->unique_id}",
                'description' => $photo->event ? "Evento: {$photo->event->name}" : "Fotografía Profesional",
                'picture_url' => $pictureUrl,
                'category_id' => 'digital_goods',
                'quantity' => 1,
                'currency_id' => 'ARS',
                'unit_price' => (float) $photo->price,
            ];
        }

        $preferenceData = [
            'items' => $items,

            'back_urls' => [
                'success' => route('payment.success', ['purchase_id' => $purchase->id]),
                'failure' => route('payment.failure', ['purchase_id' => $purchase->id]),
                'pending' => route('payment.pending', ['purchase_id' => $purchase->id]),
            ],

            'auto_return' => 'approved',
            'binary_mode' => true,
            'external_reference' => (string) $purchase->id,
            'notification_url' => $isLocal ? null : config('services.mercadopago.notification_url'),
            'statement_descriptor' => 'VISTAFY FOTOS',
        ];

        try {
            $preference = $this->preferenceClient->create($preferenceData);
            $purchase->update(['mp_preference_id' => $preference->id]);

            $isSandbox = config('services.mercadopago.test_mode');

            return [
                'success' => true,
                'purchase_id' => $purchase->id,
                'simulation_mode' => false,
                'sandbox_init_point' => $isSandbox ? $preference->sandbox_init_point : $preference->init_point,
            ];

        } catch (\Exception $e) {
            $apiError = $e->getMessage();
            $apiDetails = [];

            if (method_exists($e, 'getApiResponse') && $e->getApiResponse()) {
                $apiDetails = $e->getApiResponse()->getContent();
                Log::error('🛑 MERCADO PAGO API RESPONSE:', (array) $apiDetails);
            }

            Log::error(' [MP] Error creando preferencia de carrito', [
                'error' => $apiError,
                'details' => $apiDetails,
                'photo_count' => $photos->count(),
                'email' => $email,
            ]);

            $purchase->update(['status' => 'failed']);
            throw new \Exception("Error al procesar la compra del carrito: " . $apiError);
        }
    }


    public function processSimulatedPayment($orderToken)
    {
        $purchase = Purchase::where('order_token', $orderToken)->firstOrFail();

        $purchase->update([
            'status' => 'approved',
            'payment_id' => 'SIM-' . time(),
        ]);

        return $purchase;
    }
}
