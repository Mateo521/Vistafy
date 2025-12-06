<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('photographers', function (Blueprint $table) {
            // Campos de rechazo
            $table->text('rejection_reason')->nullable()->after('status');
            
            // Campos de suspensión (si no existen)
            if (!Schema::hasColumn('photographers', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable()->after('approved_by');
            }
            
            if (!Schema::hasColumn('photographers', 'suspended_by')) {
                $table->foreignId('suspended_by')->nullable()->constrained('users')->after('suspended_at');
            }
            
            if (!Schema::hasColumn('photographers', 'suspension_reason')) {
                $table->text('suspension_reason')->nullable()->after('suspended_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photographers', function (Blueprint $table) {
            $table->dropColumn([
                'rejection_reason',
                'suspended_at',
                'suspended_by',
                'suspension_reason',
            ]);
        });
    }
};
