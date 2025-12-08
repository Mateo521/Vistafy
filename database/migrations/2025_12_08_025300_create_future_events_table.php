<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('future_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location');
            $table->dateTime('event_date');
            $table->dateTime('expiry_date')->nullable()->comment('Fecha para convertir a evento normal');
            
            //  Thumbnail/Cover image (como en events)
            $table->string('cover_image')->nullable()->comment('Imagen de portada del evento');
            
            $table->enum('status', ['upcoming', 'converted', 'cancelled'])->default('upcoming');
            $table->foreignId('converted_event_id')->nullable()->constrained('events')->onDelete('set null');
            $table->timestamps();
            
            $table->index('event_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('future_events');
    }
};
