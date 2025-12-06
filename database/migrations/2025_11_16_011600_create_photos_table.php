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
            
            // Relaciones
            $table->foreignId('photographer_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->nullable()->constrained()->onDelete('set null'); // Puede ser nullable si la foto no pertenece a un evento aún

            // Identificadores y Info
            $table->string('unique_id')->unique(); // ID público (ej: A1B2C3)
            $table->string('title')->nullable();
            $table->text('description')->nullable();

            // Rutas de archivos
            $table->string('original_path');
            $table->string('watermarked_path')->nullable();
            $table->string('thumbnail_path')->nullable();
            
            // Metadatos técnicos
            $table->string('original_name')->nullable();
            $table->bigInteger('file_size')->nullable(); // Usar bigInteger por si son archivos grandes
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            
            // ESTA ES LA LÍNEA QUE TE FALTA:
            $table->string('mime_type')->nullable(); 

            // Negocio
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            
            // Estadísticas
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