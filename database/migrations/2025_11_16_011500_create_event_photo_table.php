<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_photo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('photo_id');
            $table->integer('order')->default(0);
            $table->timestamps();
            
            // Índices y constraints
            $table->foreign('event_id')
                  ->references('id')
                  ->on('events')
                  ->onDelete('cascade');
                  
            $table->foreign('photo_id')
                  ->references('id')
                  ->on('photos')
                  ->onDelete('cascade');
            
            $table->unique(['event_id', 'photo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_photo');
    }
};
