<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Purchase;

class VerifyPayment extends Command
{
    protected $signature = 'payment:verify {payment_id}';
    protected $description = 'Verificar un payment de Mercado Pago y actualizar la compra';

    public function handle()
    {
        $paymentId = $this->argument('payment_id');
        $token = env('MERCADOPAGO_ACCESS_TOKEN');

        $this->info("🔍 Verificando payment: {$paymentId}");

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->timeout(30)->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

            $this->line("HTTP Status: " . $response->status());

            if ($response->successful()) {
                $payment = $response->json();

                $this->info("\n✅ PAYMENT ENCONTRADO");
                $this->line("Status: " . $payment['status']);
                $this->line("Status Detail: " . ($payment['status_detail'] ?? 'N/A'));
                $this->line("Amount: $" . $payment['transaction_amount']);
                $this->line("External Reference: " . ($payment['external_reference'] ?? 'N/A'));
                $this->line("Date Created: " . $payment['date_created']);

                $purchaseId = $payment['external_reference'] ?? null;

                if ($purchaseId && $this->confirm("\n¿Actualizar Purchase #{$purchaseId} a 'completed'?", true)) {
                    $purchase = Purchase::find($purchaseId);

                    if ($purchase) {
                        $purchase->status = 'completed';
                        $purchase->mp_payment_id = $paymentId;
                        $purchase->save();

                        $this->info("\n✅ Purchase #{$purchaseId} actualizada exitosamente");
                        $this->line("Download Token: {$purchase->download_token}");
                        $this->line("URL: " . env('APP_URL') . "/downloads/{$purchase->download_token}");
                    } else {
                        $this->error("❌ Purchase #{$purchaseId} no encontrada");
                    }
                }

            } else {
                $this->error("\n❌ ERROR");
                $this->line("Response: " . $response->body());

                if ($response->status() === 404) {
                    $this->warn("\n⚠️ El payment NO EXISTE en Mercado Pago");
                    $this->line("Posibles causas:");
                    $this->line("1. El payment ID es incorrecto");
                    $this->line("2. Estás usando credenciales de una cuenta diferente");
                    $this->line("3. El pago fue rechazado/cancelado");
                }
            }

        } catch (\Exception $e) {
            $this->error("❌ Excepción: " . $e->getMessage());
        }

        return 0;
    }
}
