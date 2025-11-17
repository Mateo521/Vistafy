<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photographers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('business_name');
            $table->string('phone')->nullable();
            $table->string('region')->nullable();
            $table->string('email')->nullable();
            $table->text('bio')->nullable();
            $table->string('watermark_path')->nullable();
            $table->json('bank_account_info')->nullable();
            $table->boolean('is_verified')->default(true);
            $table->timestamps();

            // Índices
            $table->index('user_id');
            $table->index('region');
            $table->index('is_verified');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photographers');
    }
};
