<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::statement("ALTER TABLE `leave_requests` MODIFY `leave_type` VARCHAR(64) NOT NULL");
        } catch (\Throwable $e) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->string('leave_type', 64)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep as VARCHAR(64) to preserve existing leave records
    }
};
