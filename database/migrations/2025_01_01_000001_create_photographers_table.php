<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('photographers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('business_name');
            $table->string('slug')->unique();
            $table->string('phone')->nullable();
            $table->string('region')->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();


            $table->text('bio')->nullable();

            $table->string('website')->nullable();
            $table->string('instagram')->nullable();
            $table->string('facebook')->nullable();

            // Imágenes
            $table->string('profile_photo')->nullable();
            $table->string('banner_photo')->nullable();

            // Estado y Verificación
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])->default('pending');

            // Campos de administración
            $table->text('rejection_reason')->nullable();
            $table->text('suspension_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photographers');
    }
};