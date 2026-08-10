<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        
        Schema::table('event_photographer', function (Blueprint $table) {
            
            if (!Schema::hasColumn('event_photographer', 'status')) {
                $table->string('status')->default('pending')->after('photographer_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_photographer', function (Blueprint $table) {
            if (Schema::hasColumn('event_photographer', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};