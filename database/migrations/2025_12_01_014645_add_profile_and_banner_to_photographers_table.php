<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photographers', function (Blueprint $table) {
            // ✅ Verificar si NO existe antes de agregar
            if (!Schema::hasColumn('photographers', 'profile_photo')) {
                $table->string('profile_photo')->nullable()->after('bio');
            }
            
            if (!Schema::hasColumn('photographers', 'banner_photo')) {
                $table->string('banner_photo')->nullable()->after('profile_photo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('photographers', function (Blueprint $table) {
            // ✅ Verificar si existe antes de eliminar
            if (Schema::hasColumn('photographers', 'profile_photo')) {
                $table->dropColumn('profile_photo');
            }
            
            if (Schema::hasColumn('photographers', 'banner_photo')) {
                $table->dropColumn('banner_photo');
            }
        });
    }
};
