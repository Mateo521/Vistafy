<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('future_events', function (Blueprint $table) {
            $table->foreignId('photographer_id')
                  ->after('id')
                  ->constrained('photographers')
                  ->cascadeOnDelete();
                  
            $table->index('photographer_id');
        });
    }

    public function down(): void
    {
        Schema::table('future_events', function (Blueprint $table) {
            $table->dropForeign(['photographer_id']);
            $table->dropColumn('photographer_id');
        });
    }
};
