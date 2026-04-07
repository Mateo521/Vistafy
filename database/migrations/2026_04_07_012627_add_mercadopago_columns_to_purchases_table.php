<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
          
            if (!Schema::hasColumn('purchases', 'mp_payment_id')) {
                $table->string('mp_payment_id')->nullable()->after('status');
            }
            if (!Schema::hasColumn('purchases', 'mp_payment_status')) {
                $table->string('mp_payment_status')->nullable()->after('mp_payment_id');
            }
            if (!Schema::hasColumn('purchases', 'mp_preference_id')) {
                $table->string('mp_preference_id')->nullable()->after('mp_payment_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['mp_payment_id', 'mp_payment_status', 'mp_preference_id']);
        });
    }
};