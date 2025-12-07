<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            //  Solo agregar si no existe
            if (!Schema::hasColumn('purchase_items', 'download_count')) {
                $table->integer('download_count')->default(0)->after('unit_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_items', 'download_count')) {
                $table->dropColumn('download_count');
            }
        });
    }
};
