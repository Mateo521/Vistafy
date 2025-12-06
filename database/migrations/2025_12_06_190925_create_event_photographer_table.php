<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  // database/migrations/xxxx_xx_xx_create_event_photographer_table.php

public function up(): void
{
    Schema::create('event_photographer', function (Blueprint $table) {
        $table->id();
        
        // El Evento
        $table->foreignId('event_id')->constrained()->onDelete('cascade');
        
        // El Fotógrafo Colaborador
        $table->foreignId('photographer_id')->constrained()->onDelete('cascade');
        
        // Rol opcional (ej: 'editor', 'uploader')
        $table->string('role')->default('collaborator'); 
        
        $table->timestamps();

        // Evitar duplicados (un fotógrafo no puede ser colaborador 2 veces del mismo evento)
        $table->unique(['event_id', 'photographer_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_photographer');
    }
};
