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
            $table->string('buyer_email');
            $table->string('buyer_name')->default('Invitado');

        
            $table->decimal('total_amount', 10, 2); 
            $table->string('currency')->default('ARS');
            $table->string('status')->default('pending');

        
            $table->string('mp_preference_id')->nullable();
            $table->string('mp_payment_id')->nullable();
            $table->string('mp_merchant_order_id')->nullable();
            $table->json('payment_details')->nullable();
            $table->json('metadata')->nullable();

            
            $table->string('order_token')->unique(); 

            $table->softDeletes();
            $table->timestamps();
        });

      
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            
         
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            
           
            $table->foreignId('photo_id')->constrained()->onDelete('cascade');
            
            
            $table->decimal('unit_price', 10, 2);
            
         
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