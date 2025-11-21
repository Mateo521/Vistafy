<?php

namespace App\Services;

use App\Models\Photo;
use App\Models\Purchase;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Http;
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
    public function createPhotoPreference(Photo $photo, string $email): array
    {
        Log::info('🛒 Creando preferencia de pago', [
            'photo_id' => $photo->id,
            'amount' => $photo->price,
            'email' => $email,
            'user_authenticated' => auth()->check(),
        ]);

        $buyerEmail = $email;

        // Forzar buyer de prueba cuando estás en dev/local y MP_TEST_MODE activado
        if (config('mercadopago.test_mode') && app()->environment(['local', 'development'])) {
            $buyerEmail = config('mercadopago.test_buyer_email') ?: $buyerEmail;
        }

        // 🔧 CORREGIDO: Verificar si el usuario está autenticado antes de acceder a sus propiedades
        $buyerName = auth()->check() ? auth()->user()->name : 'Invitado';

        // Crear registro de compra
        $purchase = Purchase::create([
            'user_id' => auth()->id(), // NULL si es invitado
            'photo_id' => $photo->id,
            'event_id' => $photo->event_id, // Puede ser NULL
            'buyer_email' => $email,
            'buyer_name' => $buyerName, // 🔧 Corregido
            'amount' => $photo->price,
            'currency' => 'ARS',
            'status' => 'pending',
            'download_token' => Str::random(64),
        ]);

        Log::info('✅ Compra creada', [
            'purchase_id' => $purchase->id,
            'buyer_name' => $buyerName,
            'is_guest' => !auth()->check(),
        ]);
        if (config('mercadopago.test_mode') && app()->environment(['local', 'development'])) {
            $testEmail = config('mercadopago.test_buyer_email');
            if ($testEmail) {
                $buyerEmail = $testEmail;
                Log::info('🧪 Forzando email de prueba', ['test_email' => $buyerEmail]);
            }
        }
        // Preparar datos de la preferencia
        $preferenceData = [
            'items' => [
                [
                    'id' => (string) $photo->id,
                    'title' => $photo->title ?? "Foto #{$photo->id}",
                    'description' => $photo->event
                        ? "Foto del evento {$photo->event->name}"
                        : "Foto profesional",
                    'category_id' => 'digital_content',
                    'quantity' => 1,
                    'currency_id' => 'ARS',
                    'unit_price' => (float) $photo->price,
                ]
            ],
            "payer" => [
                "name" => "Test",
                "surname" => "User",
                "email" => $buyerEmail,
                "phone" => [
                    "area_code" => "11",
                    "number" => "12345678"
                ],
                "identification" => [
                    "type" => "DNI",
                    "number" => "12345678", // Usar string fijo
                ],
                "address" => [
                    "zip_code" => "1430",
                    "street_name" => "Calle",
                    "street_number" => 123,
                ]
            ],
            'back_urls' => [
                'success' => route('payment.success', ['purchase_id' => $purchase->id]),
                'failure' => route('payment.failure', ['purchase_id' => $purchase->id]),
                'pending' => route('payment.pending', ['purchase_id' => $purchase->id]),
            ],
            'auto_return' => 'approved',
            'notification_url' => route('webhooks.mercadopago'),
            'external_reference' => (string) $purchase->id,
            'statement_descriptor' => 'VISTAFY',
            'payment_methods' => [
                'installments' => 1,
                'default_installments' => 1,
            ],
        ];

        Log::info('📤 Enviando preferencia a Mercado Pago', $preferenceData);

        try {
            // Crear preferencia
            $preference = $this->preferenceClient->create($preferenceData);

            // Actualizar purchase con el preference_id
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
                'purchase_id' => $purchase->id,
                'preference_id' => $preference->id,
                'init_point' => $preference->init_point,
                'sandbox_init_point' => $preference->sandbox_init_point,
            ];

        } catch (\Exception $e) {
            // Si falla la creación de preferencia, marcar compra como fallida
            $purchase->update(['status' => 'failed']);

            Log::error('❌ Error al crear preferencia en MP', [
                'error' => $e->getMessage(),
                'purchase_id' => $purchase->id,
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
    /**
     * Procesar notificación webhook (mejorada para merchant_order y payment)
     */
    public function processWebhookNotification(array $data): void
    {
        Log::info('🔔 Webhook recibido de Mercado Pago', $data);

        // Si viene como "topic" o "type"
        $topic = $data['topic'] ?? $data['type'] ?? null;

        if ($topic === 'payment') {
            $paymentId = $data['data']['id'] ?? null;
            if (!$paymentId) {
                Log::warning('⚠️ Webhook payment sin id');
                return;
            }

            $payment = $this->getPayment($paymentId);
            if ($payment) {
                $this->handlePaymentObject($payment);
            }
            return;
        }

        if ($topic === 'merchant_order') {
            // Puede venir en data.id o id directo
            $merchantOrderId = $data['data']['id'] ?? $data['id'] ?? null;
            if (!$merchantOrderId) {
                Log::warning('⚠️ Webhook merchant_order sin id');
                return;
            }

            $merchantOrder = $this->getMerchantOrder($merchantOrderId);
            if (!$merchantOrder) {
                return;
            }

            // merchant_order puede tener payments array con id
            $payments = $merchantOrder['payments'] ?? [];
            foreach ($payments as $p) {
                $paymentId = $p['id'] ?? null;
                if (!$paymentId)
                    continue;

                $payment = $this->getPayment((string) $paymentId);
                if ($payment) {
                    $this->handlePaymentObject($payment);
                }
            }

            return;
        }

        Log::info('ℹ️ Tipo de notificación ignorada', ['topic' => $topic]);
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



    public function getMerchantOrder(string $merchantOrderId): ?array
    {
        $token = config('mercadopago.access_token');
        $res = Http::withToken($token)
            ->get("https://api.mercadopago.com/merchant_orders/{$merchantOrderId}");

        if ($res->ok()) {
            Log::info('📦 Merchant order obtenida', ['id' => $merchantOrderId]);
            return $res->json();
        }

        Log::error('❌ Error al obtener merchant_order', [
            'id' => $merchantOrderId,
            'status' => $res->status(),
            'body' => $res->body(),
        ]);

        return null;
    }

    /**
     * Procesa un payment (objeto Payment retornado por SDK)
     */
    protected function handlePaymentObject(object $payment): void
    {
        Log::info('💳 Procesando payment object', ['id' => $payment->id, 'status' => $payment->status]);

        $purchase = Purchase::find($payment->external_reference);

        if (!$purchase) {
            Log::error('❌ Compra no encontrada por external_reference', ['external_reference' => $payment->external_reference]);
            return;
        }

        $purchase->update([
            'mp_payment_id' => $payment->id,
            'mp_merchant_order_id' => $payment->order->id ?? null,
            'status' => $this->mapPaymentStatus($payment->status),
            'payment_details' => [
                'payment_method' => $payment->payment_method_id ?? null,
                'payment_type' => $payment->payment_type_id ?? null,
                'status' => $payment->status ?? null,
                'status_detail' => $payment->status_detail ?? null,
                'transaction_amount' => $payment->transaction_amount ?? null,
                'date_approved' => $payment->date_approved ?? null,
            ],
        ]);

        Log::info('✅ Compra actualizada desde payment', ['purchase_id' => $purchase->id, 'status' => $purchase->status]);
    }


    public function processPayment(Photo $photo, array $paymentData, string $email): array
    {
        Log::info('💳 Procesando pago directo', [
            'photo_id' => $photo->id,
            'amount' => $photo->price,
            'email' => $email,
        ]);

        // Crear compra
        $purchase = Purchase::create([
            'photo_id' => $photo->id,
            'buyer_email' => $email,
            'buyer_name' => $paymentData['payer']['first_name'] ?? 'Guest',
            'amount' => $photo->price,
            'status' => 'pending',
            'user_id' => auth()->id(),
            'download_token' => Str::random(64),
        ]);

        try {
            MercadoPagoConfig::setAccessToken($this->accessToken);
            $client = new PaymentClient();

            // Preparar datos del pago
            $paymentRequest = [
                'transaction_amount' => (float) $photo->price,
                'token' => $paymentData['token'],
                'description' => "Foto #{$photo->id}",
                'installments' => (int) $paymentData['installments'],
                'payment_method_id' => $paymentData['payment_method_id'],
                'issuer_id' => $paymentData['issuer_id'],
                'payer' => [
                    'email' => $email,
                    'identification' => [
                        'type' => $paymentData['payer']['identification']['type'],
                        'number' => $paymentData['payer']['identification']['number'],
                    ],
                ],
                'external_reference' => (string) $purchase->id,
                'notification_url' => route('webhooks.mercadopago'),
                'statement_descriptor' => 'VISTAFY',
            ];

            Log::info('📤 Enviando pago a MP', $paymentRequest);

            $payment = $client->create($paymentRequest);

            Log::info('✅ Pago procesado', [
                'payment_id' => $payment->id,
                'status' => $payment->status,
                'status_detail' => $payment->status_detail,
            ]);

            // Actualizar compra
            $purchase->update([
                'mp_payment_id' => $payment->id,
                'status' => $this->mapMercadoPagoStatus($payment->status),
            ]);

            return [
                'status' => $payment->status,
                'payment_id' => $payment->id,
                'purchase_id' => $purchase->id,
                'redirect_url' => $this->getRedirectUrl($payment->status, $purchase->id),
            ];

        } catch (\Exception $e) {
            Log::error('❌ Error procesando pago', [
                'error' => $e->getMessage(),
                'purchase_id' => $purchase->id,
            ]);

            $purchase->update(['status' => 'failed']);

            throw $e;
        }
    }

    private function getRedirectUrl(string $status, int $purchaseId): string
    {
        return match ($status) {
            'approved' => route('payment.success', ['purchase_id' => $purchaseId]),
            'pending', 'in_process' => route('payment.pending', ['purchase_id' => $purchaseId]),
            default => route('payment.failure', ['purchase_id' => $purchaseId]),
        };
    }

    private function mapMercadoPagoStatus(string $mpStatus): string
    {
        return match ($mpStatus) {
            'approved' => 'completed',
            'pending', 'in_process' => 'pending',
            'rejected', 'cancelled' => 'failed',
            default => 'pending',
        };
    }

}
