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
        Schema::table('photographers', function (Blueprint $table) {
            // Agregar columnas de estado si no existen
            if (!Schema::hasColumn('photographers', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_verified');
            }
            
            if (!Schema::hasColumn('photographers', 'status')) {
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photographers', function (Blueprint $table) {
            if (Schema::hasColumn('photographers', 'is_active')) {
                $table->dropColumn('is_active');
            }
            
            if (Schema::hasColumn('photographers', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
