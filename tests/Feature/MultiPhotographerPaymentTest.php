<?php

namespace Tests\Feature;

use App\Models\Photo;
use App\Models\Photographer;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\User;
use App\Notifications\PurchaseCompleted;
use App\Services\MercadoPagoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Tests\TestCase;

class MultiPhotographerPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_with_multiple_photographers_creates_one_payment_per_photographer(): void
    {
        $user = User::factory()->create();
        $photographerA = Photographer::factory()->create(['mp_access_token' => 'seller-token-a']);
        $photographerB = Photographer::factory()->create(['mp_access_token' => 'seller-token-b']);
        $photoA = Photo::factory()->create(['photographer_id' => $photographerA->id, 'price' => 10]);
        $photoB = Photo::factory()->create(['photographer_id' => $photographerB->id, 'price' => 20]);

        $this->mockMercadoPagoService();

        $response = $this->actingAs($user)->postJson(route('payment.initiate.cart'), [
            'photo_ids' => [$photoA->id, $photoB->id],
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'requires_multiple_payments' => true,
            ])
            ->assertJsonCount(2, 'payments');

        $purchase = Purchase::with('payments', 'items')->firstOrFail();

        $this->assertSame('pending', $purchase->status);
        $this->assertCount(2, $purchase->payments);
        $this->assertCount(2, $purchase->items);
        $this->assertEqualsCanonicalizing(
            [$photographerA->id, $photographerB->id],
            $purchase->payments->pluck('photographer_id')->all()
        );
        $this->assertTrue($purchase->items->every(fn ($item) => $item->purchase_payment_id !== null));
    }

    public function test_individual_purchase_does_not_create_duplicate_purchase_records(): void
    {
        $user = User::factory()->create();
        $photographer = Photographer::factory()->create(['mp_access_token' => 'seller-token']);
        $photo = Photo::factory()->create(['photographer_id' => $photographer->id, 'price' => 15]);

        $this->mockMercadoPagoService();

        $response = $this->actingAs($user)->postJson(route('payment.initiate', ['photo' => $photo->id]), [
            'email' => $user->email,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertSame(1, Purchase::count());
        $this->assertSame(1, PurchasePayment::count());
        $this->assertSame(1, Purchase::firstOrFail()->items()->count());
    }

    public function test_webhook_approves_parent_purchase_only_after_all_child_payments_are_approved(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $photographerA = Photographer::factory()->create(['mp_access_token' => 'seller-token-a']);
        $photographerB = Photographer::factory()->create(['mp_access_token' => 'seller-token-b']);
        $photoA = Photo::factory()->create(['photographer_id' => $photographerA->id, 'price' => 10, 'downloads' => 0]);
        $photoB = Photo::factory()->create(['photographer_id' => $photographerB->id, 'price' => 20, 'downloads' => 0]);

        $purchase = Purchase::create([
            'user_id' => $user->id,
            'buyer_email' => $user->email,
            'buyer_name' => $user->name,
            'total_amount' => 30,
            'currency' => 'ARS',
            'status' => 'pending',
        ]);

        $paymentA = PurchasePayment::create([
            'purchase_id' => $purchase->id,
            'photographer_id' => $photographerA->id,
            'amount' => 10,
            'platform_fee' => 1,
            'currency' => 'ARS',
            'status' => 'pending',
        ]);

        $paymentB = PurchasePayment::create([
            'purchase_id' => $purchase->id,
            'photographer_id' => $photographerB->id,
            'amount' => 20,
            'platform_fee' => 2,
            'currency' => 'ARS',
            'status' => 'pending',
        ]);

        $purchase->items()->create([
            'purchase_payment_id' => $paymentA->id,
            'photo_id' => $photoA->id,
            'unit_price' => 10,
        ]);
        $purchase->items()->create([
            'purchase_payment_id' => $paymentB->id,
            'photo_id' => $photoB->id,
            'unit_price' => 20,
        ]);

        Http::fake([
            'https://api.mercadopago.com/v1/payments/mp-payment-a' => Http::response([
                'id' => 'mp-payment-a',
                'status' => 'approved',
                'external_reference' => 'purchase_payment_'.$paymentA->id,
            ], 200),
            'https://api.mercadopago.com/v1/payments/mp-payment-b' => Http::response([
                'id' => 'mp-payment-b',
                'status' => 'approved',
                'external_reference' => 'purchase_payment_'.$paymentB->id,
            ], 200),
        ]);

        $this->postJson('/webhooks/mercadopago', [
            'type' => 'payment',
            'data' => ['id' => 'mp-payment-a'],
        ])->assertOk();

        $this->assertSame('pending', $purchase->fresh()->status);
        $this->assertSame(0, $photoA->fresh()->downloads);
        $this->assertSame(0, $photoB->fresh()->downloads);

        $this->postJson('/webhooks/mercadopago', [
            'type' => 'payment',
            'data' => ['id' => 'mp-payment-b'],
        ])->assertOk();

        $this->assertSame('approved', $purchase->fresh()->status);
        $this->assertSame(1, $photoA->fresh()->downloads);
        $this->assertSame(1, $photoB->fresh()->downloads);
        Notification::assertSentOnDemand(PurchaseCompleted::class);
    }

    private function mockMercadoPagoService(): void
    {
        $mock = Mockery::mock(MercadoPagoService::class);

        $mock->shouldReceive('calculatePlatformFee')
            ->andReturnUsing(fn (float $amount) => round($amount * 0.10, 2));

        $mock->shouldReceive('createPaymentPreference')
            ->andReturnUsing(function ($photos, string $email, Purchase $purchase, PurchasePayment $payment) {
                $payment->update([
                    'mp_preference_id' => 'pref-'.$payment->id,
                    'init_point' => 'https://mercadopago.test/pay/'.$payment->id,
                    'sandbox_init_point' => 'https://sandbox.mercadopago.test/pay/'.$payment->id,
                ]);

                if ($purchase->payments()->count() === 1) {
                    $purchase->update(['mp_preference_id' => 'pref-'.$payment->id]);
                }

                return [
                    'success' => true,
                    'purchase_id' => $purchase->id,
                    'purchase_payment_id' => $payment->id,
                    'init_point' => 'https://mercadopago.test/pay/'.$payment->id,
                    'sandbox_init_point' => 'https://sandbox.mercadopago.test/pay/'.$payment->id,
                ];
            });

        $mock->shouldReceive('createPhotoPreference')
            ->andReturnUsing(function (Photo $photo, string $email, Purchase $purchase) use ($mock) {
                $payment = PurchasePayment::create([
                    'purchase_id' => $purchase->id,
                    'photographer_id' => $photo->photographer_id,
                    'amount' => $photo->price,
                    'platform_fee' => round((float) $photo->price * 0.10, 2),
                    'currency' => $purchase->currency,
                    'status' => 'pending',
                ]);

                $purchase->items()->where('photo_id', $photo->id)->update([
                    'purchase_payment_id' => $payment->id,
                ]);

                return $mock->createPaymentPreference(collect([$photo]), $email, $purchase, $payment, false);
            });

        $this->app->instance(MercadoPagoService::class, $mock);
    }
}
