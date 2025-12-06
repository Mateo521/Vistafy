<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL no permite modificar ENUM directamente, hay que hacerlo con SQL raw
        DB::statement("ALTER TABLE `photographers` MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected', 'suspended') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir al ENUM original
        DB::statement("ALTER TABLE `photographers` MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
        
        // Cambiar cualquier fotógrafo suspendido de vuelta a rechazado
        DB::table('photographers')
            ->where('status', 'suspended')
            ->update(['status' => 'rejected']);
    }
};
