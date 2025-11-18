<?php

namespace App\Services;

use App\Models\Photo;
use App\Models\Purchase;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use Illuminate\Support\Facades\Log;

class MercadoPagoService
{
    protected PreferenceClient $preferenceClient;
    protected PaymentClient $paymentClient;

    public function __construct()
    {
        // Configurar SDK
        MercadoPagoConfig::setAccessToken(config('mercadopago.access_token'));

        $this->preferenceClient = new PreferenceClient();
        $this->paymentClient = new PaymentClient();
    }

    /**
     * Crear preferencia de pago para una foto
     */
    public function createPhotoPreference(Photo $photo, ?string $userEmail = null): array
    {
        try {
            // Email por defecto
            $buyerEmail = $userEmail ?? auth()->user()?->email ?? 'test@example.com';

            // Crear compra pendiente
            $purchase = Purchase::create([
                'user_id' => auth()->id(),
                'photo_id' => $photo->id,
                'event_id' => $photo->event_id,
                'buyer_email' => $buyerEmail,
                'buyer_name' => auth()->user()?->name ?? 'Comprador',
                'amount' => $photo->price ?? 1000,
                'currency' => 'ARS',
                'status' => 'pending',
            ]);

            // Crear item de la compra
            $purchase->items()->create([
                'photo_id' => $photo->id,
                'title' => "Foto #{$photo->id} - {$photo->event->name}",
                'description' => "Descarga en alta resolución",
                'unit_price' => $photo->price ?? 1000,
                'quantity' => 1,
            ]);

            Log::info('🛒 Creando preferencia de pago', [
                'purchase_id' => $purchase->id,
                'photo_id' => $photo->id,
                'amount' => $purchase->amount,
            ]);

            // Crear preferencia en Mercado Pago
            $preferenceData = [
                "items" => [
                    [
                        "id" => (string) $photo->id,
                        "title" => "Foto #{$photo->id}",
                        "description" => "Foto del evento {$photo->event->name}",
                        "category_id" => "digital_content",
                        "quantity" => 1,
                        "currency_id" => "ARS",
                        "unit_price" => (float) ($photo->price ?? 1000),
                    ]
                ],
                "payer" => [
                    "email" => $buyerEmail,
                ],
                "back_urls" => [
                    "success" => config('mercadopago.success_url') . "?purchase_id={$purchase->id}",
                    "failure" => config('mercadopago.failure_url') . "?purchase_id={$purchase->id}",
                    "pending" => config('mercadopago.pending_url') . "?purchase_id={$purchase->id}",
                ],
                "auto_return" => "approved",
                "notification_url" => config('mercadopago.notification_url'),
                "external_reference" => (string) $purchase->id,
                "statement_descriptor" => "VISTAFY",
            ];

            Log::info('📤 Enviando preferencia a Mercado Pago', $preferenceData);

            $preference = $this->preferenceClient->create($preferenceData);

            // Guardar preference_id en la compra
            $purchase->update([
                'mp_preference_id' => $preference->id,
            ]);

            Log::info('✅ Preferencia creada exitosamente', [
                'purchase_id' => $purchase->id,
                'preference_id' => $preference->id,
                'init_point' => $preference->init_point,
            ]);

            return [
                'success' => true,
                'preference_id' => $preference->id,
                'init_point' => $preference->init_point,
                'sandbox_init_point' => $preference->sandbox_init_point,
                'purchase_id' => $purchase->id,
            ];

        } catch (MPApiException $e) {
            Log::error('❌ Error al crear preferencia de Mercado Pago', [
                'message' => $e->getMessage(),
                'status_code' => $e->getApiResponse()->getStatusCode(),
                'content' => $e->getApiResponse()->getContent(),
            ]);

            throw $e;
        } catch (\Exception $e) {
            Log::error('❌ Error general al crear preferencia', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Obtener información de un pago
     */
    public function getPayment(string $paymentId): ?object
    {
        try {
            $payment = $this->paymentClient->get($paymentId);

            Log::info('💳 Información de pago obtenida', [
                'payment_id' => $paymentId,
                'status' => $payment->status,
            ]);

            return $payment;
        } catch (MPApiException $e) {
            Log::error('❌ Error al obtener información de pago', [
                'payment_id' => $paymentId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Procesar notificación webhook de Mercado Pago
     */
    public function processWebhookNotification(array $data): void
    {
        Log::info('🔔 Webhook recibido de Mercado Pago', $data);

        if (!isset($data['type']) || $data['type'] !== 'payment') {
            Log::info('⚠️ Webhook ignorado - tipo no es payment');
            return;
        }

        $paymentId = $data['data']['id'] ?? null;

        if (!$paymentId) {
            Log::warning('⚠️ Webhook sin payment ID');
            return;
        }

        // Obtener información del pago
        $payment = $this->getPayment($paymentId);

        if (!$payment) {
            Log::error('❌ No se pudo obtener información del pago', ['payment_id' => $paymentId]);
            return;
        }

        // Buscar la compra por external_reference
        $purchase = Purchase::find($payment->external_reference);

        if (!$purchase) {
            Log::error('❌ Compra no encontrada', [
                'external_reference' => $payment->external_reference,
                'payment_id' => $paymentId,
            ]);
            return;
        }

        // Actualizar compra
        $purchase->update([
            'mp_payment_id' => $paymentId,
            'mp_merchant_order_id' => $payment->order->id ?? null,
            'status' => $this->mapPaymentStatus($payment->status),
            'payment_details' => [
                'payment_method' => $payment->payment_method_id,
                'payment_type' => $payment->payment_type_id,
                'status' => $payment->status,
                'status_detail' => $payment->status_detail,
                'transaction_amount' => $payment->transaction_amount,
                'date_approved' => $payment->date_approved,
            ],
        ]);

        Log::info('✅ Compra actualizada desde webhook', [
            'purchase_id' => $purchase->id,
            'payment_id' => $paymentId,
            'status' => $purchase->status,
        ]);
    }

    /**
     * Mapear estado de pago de Mercado Pago a nuestro sistema
     */
    protected function mapPaymentStatus(string $mpStatus): string
    {
        return match ($mpStatus) {
            'approved' => 'approved',
            'pending', 'in_process', 'in_mediation' => 'in_process',
            'rejected' => 'rejected',
            'cancelled' => 'cancelled',
            'refunded', 'charged_back' => 'refunded',
            default => 'pending',
        };
    }
}
