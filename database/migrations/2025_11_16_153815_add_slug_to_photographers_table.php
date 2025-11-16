<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Eliminar índice si existe
        try {
            DB::statement('ALTER TABLE photographers DROP INDEX photographers_slug_unique');
        } catch (\Exception $e) {
            // No hacer nada si no existe
        }

        // 2. Agregar columna si no existe
        if (!Schema::hasColumn('photographers', 'slug')) {
            Schema::table('photographers', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('business_name');
            });
        }

        // 3. Generar slugs para fotógrafos existentes
        $photographers = DB::table('photographers')->whereNull('slug')->get();
        
        foreach ($photographers as$photographer) {
            $baseSlug = Str::slug($photographer->business_name);
            $slug =$baseSlug . '-' . Str::random(6);
            
            // Asegurar que sea único
            while (DB::table('photographers')->where('slug', $slug)->exists()) {
                $slug =$baseSlug . '-' . Str::random(6);
            }
            
            DB::table('photographers')
                ->where('id', $photographer->id)
                ->update(['slug' => $slug]);
        }

        // 4. Agregar índice unique de forma segura
        DB::statement('ALTER TABLE photographers ADD UNIQUE photographers_slug_unique(slug)');

        // 5. Hacer NOT NULL
        Schema::table('photographers', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('photographers', function (Blueprint $table) {
            if (Schema::hasColumn('photographers', 'slug')) {
                $table->dropUnique('photographers_slug_unique');
                $table->dropColumn('slug');
            }
        });
    }
};
