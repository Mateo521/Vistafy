<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            // Solo agregar las columnas que NO existen
            if (!Schema::hasColumn('photos', 'file_path')) {
                $table->string('file_path')->after('description')->nullable();
            }
            
            if (!Schema::hasColumn('photos', 'preview_path')) {
                $table->string('preview_path')->after('file_path')->nullable();
            }
            
            if (!Schema::hasColumn('photos', 'original_name')) {
                $table->string('original_name')->after('thumbnail_path')->nullable();
            }
            
            if (!Schema::hasColumn('photos', 'file_size')) {
                $table->unsignedBigInteger('file_size')->after('original_name')->default(0);
            }
            
            if (!Schema::hasColumn('photos', 'width')) {
                $table->unsignedInteger('width')->after('file_size')->default(0);
            }
            
            if (!Schema::hasColumn('photos', 'height')) {
                $table->unsignedInteger('height')->after('width')->default(0);
            }

            if (!Schema::hasColumn('photos', 'downloads')) {
                $table->unsignedInteger('downloads')->after('price')->default(0);
            }

            if (!Schema::hasColumn('photos', 'is_active')) {
                $table->boolean('is_active')->after('downloads')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            $columns = ['file_path', 'preview_path', 'original_name', 'file_size', 'width', 'height'];
            
            foreach ($columns as$column) {
                if (Schema::hasColumn('photos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
