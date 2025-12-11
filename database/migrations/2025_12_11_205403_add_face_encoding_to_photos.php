<?php
// database/migrations/xxxx_add_face_encoding_to_photos.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('photos', function (Blueprint $table) {
            // Almacenar múltiples caras por foto (JSON array)
            $table->json('face_encodings')->nullable()->after('thumbnail_path');
            
            // Flag para búsquedas rápidas
            $table->boolean('has_faces')->default(false)->index()->after('face_encodings');
            
            // Timestamp de cuando se procesó
            $table->timestamp('faces_processed_at')->nullable()->after('has_faces');
        });
    }

    public function down()
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->dropColumn(['face_encodings', 'has_faces', 'faces_processed_at']);
        });
    }
};
