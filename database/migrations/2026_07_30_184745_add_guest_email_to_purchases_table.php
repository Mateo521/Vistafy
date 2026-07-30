<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            if (! Schema::hasColumn('purchases', 'guest_email')) {
                $table->string('guest_email')->nullable()->after('buyer_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            if (Schema::hasColumn('purchases', 'guest_email')) {
                $table->dropColumn('guest_email');
            }
        });
    }
};
