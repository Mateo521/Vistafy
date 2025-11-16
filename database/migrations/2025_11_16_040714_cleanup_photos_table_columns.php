<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            // Eliminar columnas antiguas que ya no usamos
            if (Schema::hasColumn('photos', 'file_path')) {
                $table->dropColumn('file_path');
            }
            if (Schema::hasColumn('photos', 'preview_path')) {
                $table->dropColumn('preview_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->string('file_path')->nullable();
            $table->string('preview_path')->nullable();
        });
    }
};
