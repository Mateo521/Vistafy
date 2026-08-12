<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('future_event_photographer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('future_event_id')->constrained()->onDelete('cascade');
            $table->foreignId('photographer_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('requested'); // 'requested', 'approved', 'rejected'
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('future_event_photographer');
    }
};