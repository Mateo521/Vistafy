<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            //  Verificar si cada columna existe antes de agregarla
            if (!Schema::hasColumn('photos', 'bib_numbers')) {
                $table->json('bib_numbers')->nullable()->after('face_encodings');
            }
            
            if (!Schema::hasColumn('photos', 'bib_processed')) {
                $table->boolean('bib_processed')->default(false)->after('faces_processed_at');
            }
            
            if (!Schema::hasColumn('photos', 'bib_processed_at')) {
                $table->timestamp('bib_processed_at')->nullable()->after('bib_processed');
            }
            
            // Index solo si no existe
            if (!Schema::hasColumn('photos', 'bib_processed') || 
                !DB::select("SHOW INDEX FROM photos WHERE Key_name = 'photos_bib_processed_index'")) {
                $table->index('bib_processed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            $columns = ['bib_numbers', 'bib_processed', 'bib_processed_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('photos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
