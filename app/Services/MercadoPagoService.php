<?php

namespace App\Services;

use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Purchase;
use App\Models\PurchasePayment;

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
    public function createPaymentPreference($photos, string $email, Purchase $purchase, PurchasePayment $payment, bool $returnToSplitCheckout = false): array
    {
        return $this->buildPreference($photos, $purchase, $email, $payment, $returnToSplitCheckout);
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


    public function createPhotoPreference($photo, string $email, Purchase $purchase): array
    {
        $payment = PurchasePayment::create([
            'purchase_id' => $purchase->id,
            'photographer_id' => $photo->photographer_id,
            'amount' => $photo->price,
            'platform_fee' => $this->calculatePlatformFee((float) $photo->price),
            'currency' => $purchase->currency,
            'status' => 'pending',
        ]);

        $purchase->items()
            ->where('photo_id', $photo->id)
            ->update(['purchase_payment_id' => $payment->id]);

        return $this->createPaymentPreference(collect([$photo]), $email, $purchase, $payment, false);
    }


    public function createCartPreference($photos, string $email, Purchase $purchase): array
    {
        $photographerIds = $photos->pluck('photographer_id')->unique();

        if ($photographerIds->count() !== 1) {
            throw new \Exception('La preferencia de carrito solo puede contener fotos de un fotógrafo.');
        }

        $photographerId = $photographerIds->first();
        $totalAmount = (float) $photos->sum('price');

        $payment = PurchasePayment::create([
            'purchase_id' => $purchase->id,
            'photographer_id' => $photographerId,
            'amount' => $totalAmount,
            'platform_fee' => $this->calculatePlatformFee($totalAmount),
            'currency' => $purchase->currency,
            'status' => 'pending',
        ]);

        $purchase->items()
            ->whereIn('photo_id', $photos->pluck('id'))
            ->update(['purchase_payment_id' => $payment->id]);

        return $this->createPaymentPreference($photos, $email, $purchase, $payment, false);
    }


    public function calculatePlatformFee(float $amount): float
    {
        $comisionPorcentaje = 0.10;

        return round($amount * $comisionPorcentaje, 2);
    }


    private function buildPreference($photos, $purchase, $email, PurchasePayment $payment, bool $returnToSplitCheckout): array
    {
        $isLocal = app()->environment(['local', 'development']);
        $items = [];
        $totalAmount = 0;

        $photographerIds = $photos->pluck('photographer_id')->unique();

        if ($photographerIds->count() !== 1) {
            Log::error('Intento de crear preferencia con múltiples fotógrafos', [
                'purchase_id' => $purchase->id,
                'photographer_ids' => $photographerIds->values()->all(),
            ]);
            throw new \Exception('Cada preferencia de Mercado Pago debe pertenecer a un único fotógrafo.');
        }

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

        $platformFee = $this->calculatePlatformFee($totalAmount);
        $externalReference = 'purchase_payment_' . $payment->id;
        $backUrls = $returnToSplitCheckout
            ? [
                'success' => route('payment.split.show', ['purchase' => $purchase->id]),
                'failure' => route('payment.split.show', ['purchase' => $purchase->id]),
                'pending' => route('payment.split.show', ['purchase' => $purchase->id]),
            ]
            : [
                'success' => route('payment.success', ['purchase_id' => $purchase->id]),
                'failure' => route('payment.failure', ['purchase_id' => $purchase->id]),
                'pending' => route('payment.pending', ['purchase_id' => $purchase->id]),
            ];

        $preferenceData = [
            'items' => $items,
            'marketplace_fee' => $platformFee, // <--- ACÁ ESTÁ LA MAGIA DEL SPLIT
            'payer' => [
                'email' => $email,
            ],
            'back_urls' => $backUrls,
            'auto_return' => 'approved',
            'binary_mode' => true,
            'external_reference' => $externalReference,
            'notification_url' => $isLocal ? null : config('services.mercadopago.notification_url'),
            'statement_descriptor' => 'F33 FOTOS',
        ];

        try {

            $preference = $this->preferenceClient->create($preferenceData);
            $payment->update([
                'platform_fee' => $platformFee,
                'mp_preference_id' => $preference->id,
                'init_point' => $preference->init_point,
                'sandbox_init_point' => $preference->sandbox_init_point,
            ]);

            if ($purchase->payments()->count() === 1) {
                $purchase->update(['mp_preference_id' => $preference->id]);
            }


            MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));

            return [
                'success' => true,
                'purchase_id' => $purchase->id,
                'purchase_payment_id' => $payment->id,
                'init_point' => $preference->init_point,
                'sandbox_init_point' => $preference->sandbox_init_point,
            ];

        } catch (\Exception $e) {
            $payment->update(['status' => 'failed']);
            Log::error('[MP] Error creando preferencia Marketplace', [
                'error' => $e->getMessage(),
                'email' => $email,
                'purchase_id' => $purchase->id,
                'purchase_payment_id' => $payment->id,
            ]);
            throw new \Exception("Error comunicándose con Mercado Pago.");
        }
    }
}