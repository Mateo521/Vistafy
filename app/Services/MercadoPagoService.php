<?php

namespace App\Services;

use App\Models\Photo;
use App\Models\Purchase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoService
{
    protected $preferenceClient;

    public function __construct()
    {
        // Solo configurar MP si NO estamos en modo simulación
        if (!$this->isSimulationMode()) {
            MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));
            $this->preferenceClient = new PreferenceClient();
        }
    }

    /**
     * Detectar si estamos en modo simulación (local sin túnel)
     */
    protected function isSimulationMode(): bool
    {
        return config('app.env') === 'local' && config('services.mercadopago.simulation_mode', false);
    }

    /**
     * Crear preferencia de pago para una foto
     */
    public function createPhotoPreference(Photo $photo, string $email): array
    {
        $buyerName = auth()->check() ? auth()->user()->name : 'Invitado';

        // 1. Crear compra en BD
        $purchase = Purchase::create([
            'user_id' => auth()->id(),
            'buyer_email' => $email,
            'buyer_name' => $buyerName,
            'total_amount' => $photo->price,
            'currency' => 'ARS',
            'status' => 'pending',
        ]);

        // 2. Crear item
        $purchase->items()->create([
            'photo_id' => $photo->id,
            'unit_price' => $photo->price,
        ]);

        //  MODO SIMULACIÓN (LOCAL)
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

        // 🌐 MODO REAL (PRODUCCIÓN / STAGING)
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
     * Procesar pago simulado (solo en desarrollo)
     */
    public function processSimulatedPayment(Purchase $purchase, string $status = 'approved'): array
    {
        if (!$this->isSimulationMode()) {
            throw new \Exception('La simulación solo está disponible en desarrollo local');
        }

        $statusMap = [
            'approved' => 'completed',
            'rejected' => 'failed',
            'pending' => 'pending',
        ];

        $newStatus = $statusMap[$status] ?? 'failed';

        $purchase->update([
            'status' => $newStatus,
            'mp_payment_id' => 'SIM-' . Str::random(10),
            'payment_type' => 'simulated',
            'payment_status' => $status,
            'paid_at' => $status === 'approved' ? now() : null,
        ]);

        Log::info('🧪 [MP SIMULATION] Pago procesado', [
            'purchase_id' => $purchase->id,
            'status' => $status,
            'new_status' => $newStatus,
        ]);

        return [
            'success' => true,
            'status' => $status,
            'purchase' => $purchase,
        ];
    }
}
