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
        //  Corregido
        $accessToken = config('services.mercadopago.access_token');

        //  LOG para debug
        Log::info(' [MercadoPagoService] Configurando SDK', [
            'token_preview' => substr($accessToken, 0, 30) . '...',
        ]);

        MercadoPagoConfig::setAccessToken($accessToken);

        $this->preferenceClient = new PreferenceClient();
        $this->paymentClient = new PaymentClient();
    }


    /**
     * Crear preferencia de pago para una foto
     */
    public function createPhotoPreference(Photo $photo, string $email): array
    {
        Log::info(' Creando preferencia de pago', [
            'photo_id' => $photo->id,
            'amount' => $photo->price,
            'email' => $email,
            'user_authenticated' => auth()->check(),
        ]);

        $buyerEmail = $email;

        // Forzar buyer de prueba cuando estás en dev/local y MP_TEST_MODE activado
        if (config('services.mercadopago.test_mode') && app()->environment(['local', 'development'])) {
            $buyerEmail = config('services.mercadopago.test_buyer_email') ?: $buyerEmail;
        }

        //  CORREGIDO: Verificar si el usuario está autenticado antes de acceder a sus propiedades
        $buyerName = auth()->check() ? auth()->user()->name : 'Invitado';

        // Crear registro de compra
        $purchase = Purchase::create([
            'user_id' => auth()->id(), // NULL si es invitado
            'photo_id' => $photo->id,
            'event_id' => $photo->event_id, // Puede ser NULL
            'buyer_email' => $email,
            'buyer_name' => $buyerName, //  Corregido
            'amount' => $photo->price,
            'currency' => 'ARS',
            'status' => 'pending',
            'download_token' => Str::random(64),
        ]);

        Log::info(' Compra creada', [
            'purchase_id' => $purchase->id,
            'buyer_name' => $buyerName,
            'is_guest' => !auth()->check(),
        ]);
        if (config('services.mercadopago.test_mode') && app()->environment(['local', 'development'])) {
            $testEmail = config('services.mercadopago.test_buyer_email');
            if ($testEmail) {
                $buyerEmail = $testEmail;
                Log::info(' Forzando email de prueba', ['test_email' => $buyerEmail]);
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
            'payer' => [
                'email' => $buyerEmail,
                'identification' => [
                    'type' => 'DNI',
                    'number' => config('services.mercadopago.test_buyer_dni', '12345678'), // o null si no aplica
                ],
            ],
            'back_urls' => [
                'success' => route('payment.success', ['purchase_id' => $purchase->id]),
                'failure' => route('payment.failure', ['purchase_id' => $purchase->id]),
                'pending' => route('payment.pending', ['purchase_id' => $purchase->id]),
            ],
            'auto_return' => 'approved',
            'binary_mode' => false,
            'notification_url' => route('webhooks.mercadopago'),
            'external_reference' => (string) $purchase->id,
            // 'statement_descriptor' => 'VISTAFY',
            'payment_methods' => [
                'installments' => 1,
                'default_installments' => 1,
            ],
        ];

        Log::info(' Enviando preferencia a Mercado Pago', $preferenceData);

        try {
            // Crear preferencia
            $preference = $this->preferenceClient->create($preferenceData);

            $useSandbox = config('services.mercadopago.test_mode') && app()->environment(['local', 'development']);
            $initPoint = $useSandbox ? ($preference->sandbox_init_point ?? $preference->init_point) : $preference->init_point;


            // Actualizar purchase con el preference_id
            $purchase->update([
                'mp_preference_id' => $preference->id,
            ]);

            Log::info(' Preferencia creada exitosamente', [
                'purchase_id' => $purchase->id,
                'preference_id' => $preference->id,
                'init_point' => $preference->init_point,
            ]);

            return [
                'success' => true,
                'purchase_id' => $purchase->id,
                'preference_id' => $preference->id,
                'init_point' => $initPoint,
                'sandbox_init_point' => $preference->sandbox_init_point,
            ];

        } catch (\Exception $e) {
            // Si falla la creación de preferencia, marcar compra como fallida
            $purchase->update(['status' => 'failed']);

            Log::error(' Error al crear preferencia en MP', [
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

            Log::info(' Información de pago obtenida', [
                'payment_id' => $paymentId,
                'status' => $payment->status,
            ]);

            return $payment;
        } catch (MPApiException $e) {
            Log::error(' Error al obtener información de pago', [
                'payment_id' => $paymentId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }


    public function processWebhookNotification(array $data): void
    {
        Log::info('🔔 Webhook recibido de Mercado Pago', $data);

        $topic = $data['topic'] ?? $data['type'] ?? null;

        if ($topic === 'payment') {
            $paymentId = $data['data']['id'] ?? $data['id'] ?? null;
            if (!$paymentId) {
                Log::warning('⚠️ Webhook payment sin id');
                return;
            }

            Log::info('💳 Procesando payment webhook', ['payment_id' => $paymentId]);

            $payment = $this->getPayment((string) $paymentId);
            if ($payment) {
                $this->handlePaymentObject($payment);
            }
            return;
        }

        if ($topic === 'merchant_order') {
            $merchantOrderId = $data['data']['id'] ?? $data['id'] ?? null;
            if (!$merchantOrderId) {
                Log::warning('⚠️ Webhook merchant_order sin id');
                return;
            }

            Log::info('📦 Procesando merchant_order webhook', ['merchant_order_id' => $merchantOrderId]);

            $merchantOrder = $this->getMerchantOrder((string) $merchantOrderId);
            if (!$merchantOrder) {
                return;
            }

            // Procesar los payments dentro del merchant_order
            $payments = $merchantOrder['payments'] ?? [];

            Log::info('💰 Payments en merchant_order', ['count' => count($payments), 'payments' => $payments]);

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
            'approved' => 'approved', //  Correcto
            'pending', 'in_process', 'in_mediation' => 'in_process',
            'rejected' => 'rejected',
            'cancelled' => 'cancelled',
            'refunded', 'charged_back' => 'refunded',
            default => 'pending',
        };
    }




    public function getMerchantOrder(string $merchantOrderId): ?array
    {
        $token = config('services.mercadopago.access_token');
        $res = Http::withToken($token)
            ->get("https://api.mercadopago.com/merchant_orders/{$merchantOrderId}");

        if ($res->ok()) {
            Log::info(' Merchant order obtenida', ['id' => $merchantOrderId]);
            return $res->json();
        }

        Log::error(' Error al obtener merchant_order', [
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
        Log::info('🔍 Procesando payment object', [
            'id' => $payment->id,
            'status' => $payment->status,
            'status_detail' => $payment->status_detail ?? 'N/A',
            'external_reference' => $payment->external_reference ?? 'N/A'
        ]);

        if (!isset($payment->external_reference)) {
            Log::error('❌ Payment sin external_reference', ['payment_id' => $payment->id]);
            return;
        }

        $purchase = Purchase::find($payment->external_reference);

        if (!$purchase) {
            Log::error('❌ Compra no encontrada', ['external_reference' => $payment->external_reference]);
            return;
        }

        $newStatus = $this->mapPaymentStatus($payment->status);

        $purchase->update([
            'mp_payment_id' => $payment->id,
            'mp_merchant_order_id' => $payment->order->id ?? null,
            'status' => $newStatus,
            'payment_details' => [
                'payment_method' => $payment->payment_method_id ?? null,
                'payment_type' => $payment->payment_type_id ?? null,
                'status' => $payment->status ?? null,
                'status_detail' => $payment->status_detail ?? null,
                'transaction_amount' => $payment->transaction_amount ?? null,
                'date_approved' => $payment->date_approved ?? null,
            ],
        ]);

        Log::info('✅ Compra actualizada desde payment', [
            'purchase_id' => $purchase->id,
            'old_status' => $purchase->getOriginal('status'),
            'new_status' => $newStatus,
            'payment_status' => $payment->status,
            'status_detail' => $payment->status_detail ?? 'N/A'
        ]);
    }



    public function processPayment(Photo $photo, array $paymentData, string $email): array
    {
        Log::info(' Procesando pago directo', [
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
            $accessToken = config('services.mercadopago.access_token');
            MercadoPagoConfig::setAccessToken($accessToken);
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

            Log::info(' Enviando pago a MP', $paymentRequest);

            $payment = $client->create($paymentRequest);

            Log::info(' Pago procesado', [
                'payment_id' => $payment->id,
                'status' => $payment->status,
                'status_detail' => $payment->status_detail,
            ]);

            // Actualizar compra
            $purchase->update([
                'mp_payment_id' => $payment->id,
                'status' => $this->mapPaymentStatus($payment->status), // 
            ]);

            return [
                'status' => $payment->status,
                'payment_id' => $payment->id,
                'purchase_id' => $purchase->id,
                'redirect_url' => $this->getRedirectUrl($payment->status, $purchase->id),
            ];

        } catch (\Exception $e) {
            Log::error(' Error procesando pago', [
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



}
