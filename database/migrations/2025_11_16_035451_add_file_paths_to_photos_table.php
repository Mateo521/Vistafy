<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            if (!Schema::hasColumn('photos', 'file_path')) {
                $table->string('file_path')->after('description');
            }
            if (!Schema::hasColumn('photos', 'preview_path')) {
                $table->string('preview_path')->after('file_path');
            }
            if (!Schema::hasColumn('photos', 'thumbnail_path')) {
                $table->string('thumbnail_path')->after('preview_path');
            }
            if (!Schema::hasColumn('photos', 'original_name')) {
                $table->string('original_name')->after('thumbnail_path');
            }
            if (!Schema::hasColumn('photos', 'file_size')) {
                $table->unsignedBigInteger('file_size')->after('original_name');
            }
            if (!Schema::hasColumn('photos', 'width')) {
                $table->unsignedInteger('width')->after('file_size');
            }
            if (!Schema::hasColumn('photos', 'height')) {
                $table->unsignedInteger('height')->after('width');
            }
        });
    }

    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->dropColumn([
                'file_path',
                'preview_path',
                'thumbnail_path',
                'original_name',
                'file_size',
                'width',
                'height',
            ]);
        });
    }
};
