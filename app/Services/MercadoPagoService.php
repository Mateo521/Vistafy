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
            throw new \Exception('Mercado Pago access token no configurado en el archivo .env');
        }

        MercadoPagoConfig::setAccessToken($accessToken);
        MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);

        $this->preferenceClient = new PreferenceClient();
        $this->paymentClient = new PaymentClient();
    }


    public function getPayment($paymentId)
    {
        try {
            $payment = $this->paymentClient->get($paymentId);
            return $payment;
        } catch (\Exception $e) {
            Log::error('Error obteniendo payment de MP', ['payment_id' => $paymentId, 'error' => $e->getMessage()]);
            return null;
        }
    }


    public function createPhotoPreference($photo, string $email): array
    {
        $buyerName = auth()->check() ? auth()->user()->name : 'Invitado';

        // 1. Crear compra pendiente en tu BD
        $purchase = Purchase::create([
            'user_id' => auth()->id(),
            'buyer_email' => $email,
            'buyer_name' => $buyerName,
            'total_amount' => $photo->price,
            'currency' => 'ARS',
            'status' => 'pending',
            'order_token' => Str::random(64),
        ]);

        $purchase->items()->create([
            'photo_id' => $photo->id,
            'unit_price' => $photo->price,
        ]);

        return $this->buildPreference(collect([$photo]), $purchase, $email);
    }


    public function createCartPreference($photos, string $email, Purchase $purchase): array
    {
        return $this->buildPreference($photos, $purchase, $email);
    }


    private function buildPreference($photos, $purchase, $email): array
    {
        $isLocal = app()->environment(['local', 'development']);
        $items = [];
        $totalAmount = 0;

        $photographer = $photos->first()->photographer;

        if (!$photographer || !$photographer->mp_access_token) {
            Log::error('Intento de compra sin cuenta vinculada', ['photographer_id' => $photographer->id ?? 'N/A']);
            throw new \Exception("El fotógrafo no puede recibir pagos en este momento.");
        }

        MercadoPagoConfig::setAccessToken($photographer->mp_access_token);

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

            $totalAmount += (float) $photo->price;
        }

        // Si la foto vale 5000, $platformFee será 500. 
        // El fotógrafo recibe 4500 y vos recibís 500.
        $comisionPorcentaje = 0.10; 
        $platformFee = $totalAmount * $comisionPorcentaje;

        $preferenceData = [
            'items' => $items,
            'marketplace_fee' => $platformFee, // <--- ACÁ ESTÁ LA MAGIA DEL SPLIT
            'payer' => [
                'email' => $email,
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
            'statement_descriptor' => 'F33 FOTOS',
        ];

        try {

            $preference = $this->preferenceClient->create($preferenceData);

            $purchase->update(['mp_preference_id' => $preference->id]);
            $isSandbox = config('services.mercadopago.test_mode');


            MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));

            return [
                'success' => true,
                'purchase_id' => $purchase->id,
                'init_point' => $preference->init_point,
                'sandbox_init_point' => $preference->sandbox_init_point,
            ];

        } catch (\Exception $e) {
            $purchase->update(['status' => 'failed']);
            Log::error('[MP] Error creando preferencia Marketplace', [
                'error' => $e->getMessage(),
                'email' => $email,
            ]);
            throw new \Exception("Error comunicándose con Mercado Pago.");
        }
    }
}