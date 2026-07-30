<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            $table->foreignId('photographer_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->decimal('platform_fee', 10, 2)->default(0);
            $table->string('currency')->default('ARS');
            $table->string('status')->default('pending');
            $table->string('mp_preference_id')->nullable();
            $table->string('mp_payment_id')->nullable();
            $table->string('mp_payment_status')->nullable();
            $table->text('init_point')->nullable();
            $table->text('sandbox_init_point')->nullable();
            $table->json('payment_details')->nullable();
            $table->timestamps();

            $table->index(['purchase_id', 'status']);
            $table->index(['photographer_id', 'status']);
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->foreignId('purchase_payment_id')
                ->nullable()
                ->after('purchase_id')
                ->constrained('purchase_payments')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchase_payment_id');
        });

        Schema::dropIfExists('purchase_payments');
    }
};
