<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Modificar el ENUM para incluir 'failed'
        DB::statement("ALTER TABLE purchases MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'cancelled', 'in_process', 'failed') NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        // Revertir al ENUM original
        DB::statement("ALTER TABLE purchases MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'cancelled', 'in_process') NOT NULL DEFAULT 'pending'");
    }
};
