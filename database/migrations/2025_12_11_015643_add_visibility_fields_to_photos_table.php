<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            // Solo agregar is_private si no existe
            if (!Schema::hasColumn('photos', 'is_private')) {
                $table->boolean('is_private')->default(false)->after('height');
            }
            
            // Solo agregar is_active si no existe
            if (!Schema::hasColumn('photos', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_private');
            }
        });
    }

    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            if (Schema::hasColumn('photos', 'is_private')) {
                $table->dropColumn('is_private');
            }
            
            if (Schema::hasColumn('photos', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
