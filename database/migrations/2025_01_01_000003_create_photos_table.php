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
            $table->foreignId('photographer_id')->constrained()->onDelete('cascade');
            // Aquí definimos la relación directa con eventos (1 a muchos)
            $table->foreignId('event_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string('unique_id')->unique();
            $table->string('title')->nullable();
            $table->text('description')->nullable();

            // Paths
            $table->string('original_path'); // URL o path local
            $table->string('watermarked_path')->nullable();
            $table->string('thumbnail_path')->nullable();
            
            // Metadata
            $table->string('original_name')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('mime_type')->nullable();

            // Negocio
            $table->decimal('price', 10, 2)->default(5.00);
            $table->boolean('is_active')->default(true);
            
            $table->integer('downloads')->default(0);
            $table->integer('views')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};