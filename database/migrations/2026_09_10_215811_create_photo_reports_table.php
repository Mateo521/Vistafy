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
    Schema::create('photo_reports', function (Blueprint $table) {
        $table->id();
        $table->foreignId('photo_id')->constrained()->cascadeOnDelete();
        $table->string('email');  
        $table->string('reason'); // 'copyright', 'privacidad', 'inapropiado'
        $table->text('message')->nullable();
        $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_reports');
    }
};
