<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'separation_date')) {
                $table->date('separation_date')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('employees', 'separation_reason')) {
                $table->string('separation_reason')->nullable()->after('separation_date');
            }
            if (!Schema::hasColumn('employees', 'separation_notes')) {
                $table->text('separation_notes')->nullable()->after('separation_reason');
            }
        });

        // Restore any previously soft-deleted employees so their historical records are preserved 100%
        DB::table('employees')
            ->whereNotNull('deleted_at')
            ->update([
                'is_active' => false,
                'separation_reason' => DB::raw("COALESCE(separation_reason, 'Resigned / Inactive')"),
                'deleted_at' => null,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['separation_date', 'separation_reason', 'separation_notes']);
        });
    }
};
