<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('photo_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            
            // Datos del comprador
            $table->string('buyer_email');
            $table->string('buyer_name')->nullable();
            
            // Datos de Mercado Pago
            $table->string('mp_preference_id')->nullable();
            $table->string('mp_payment_id')->nullable()->unique();
            $table->string('mp_merchant_order_id')->nullable();
            
            // Estado y montos
            $table->enum('status', [
                'pending',      // Esperando pago
                'approved',     // Aprobado
                'in_process',   // En proceso
                'rejected',     // Rechazado
                'cancelled',    // Cancelado
                'refunded'      // Reembolsado
            ])->default('pending');
            
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('ARS');
            
            // Metadatos
            $table->json('payment_details')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Descarga
            $table->timestamp('downloaded_at')->nullable();
            $table->integer('download_count')->default(0);
            $table->string('download_token')->unique()->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('user_id');
            $table->index('photo_id');
            $table->index('event_id');
            $table->index('mp_payment_id');
            $table->index('status');
            $table->index('buyer_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
