<?php
// database/migrations/xxxx_add_bib_numbers_to_photos_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->json('bib_numbers')->nullable()->after('face_encodings');
            $table->boolean('bib_processed')->default(false)->after('faces_processed_at');
            $table->timestamp('bib_processed_at')->nullable()->after('bib_processed');
            
            $table->index('bib_processed');
        });
    }

    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->dropColumn(['bib_numbers', 'bib_processed', 'bib_processed_at']);
        });
    }
};
