<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id')->unique(); // ID única visible (ej: PHT-001234)
            $table->foreignId('photographer_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2); // Precio base
            $table->string('original_path'); // Ruta archivo original
            $table->string('watermarked_path'); // Ruta con marca de agua
            $table->string('thumbnail_path')->nullable(); // Miniatura
            $table->boolean('is_active')->default(true);
            $table->integer('downloads')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('unique_id');
            $table->index('photographer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
