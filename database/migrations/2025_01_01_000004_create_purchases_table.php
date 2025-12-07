<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. TABLA CABECERA (La Orden de Compra)
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            
            // Quién compra
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('buyer_email');
            $table->string('buyer_name')->default('Invitado');

            // Totales
            $table->decimal('total_amount', 10, 2); // El total de todo el carrito
            $table->string('currency')->default('ARS');
            $table->string('status')->default('pending');

            // Mercado Pago
            $table->string('mp_preference_id')->nullable();
            $table->string('mp_payment_id')->nullable();
            $table->string('mp_merchant_order_id')->nullable();
            $table->json('payment_details')->nullable();
            $table->json('metadata')->nullable();

            // Token global de la orden (para ver el recibo/descargas)
            $table->string('order_token')->unique(); 

            $table->softDeletes();
            $table->timestamps();
        });

        // 2. TABLA DETALLE (Los Items del Carrito)
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            
            // Relación con la cabecera
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            
            // Relación con la foto y el evento
            $table->foreignId('photo_id')->constrained()->onDelete('cascade');
            
            // Guardamos el precio al momento de la compra (por si cambia después)
            $table->decimal('unit_price', 10, 2);
            
            // Token individual por si quieres validar descargas por foto
            $table->string('download_token')->unique(); 
            $table->integer('download_count')->default(0);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};