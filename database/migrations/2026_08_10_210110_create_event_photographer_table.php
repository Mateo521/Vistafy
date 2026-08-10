<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('event_photographer', function (Blueprint $table) {
        $table->id();
        $table->foreignId('event_id')->constrained()->onDelete('cascade');
        $table->foreignId('photographer_id')->constrained()->onDelete('cascade');
        // status: 'invited', 'applied', 'approved', 'rejected'
        $table->string('status')->default('pending');
        $table->timestamps();
        
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
