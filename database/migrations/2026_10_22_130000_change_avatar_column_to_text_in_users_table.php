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
            DB::statement("ALTER TABLE `users` MODIFY `avatar` TEXT NULL");
        } catch (\Throwable $e) {
            // Fallback for non-MySQL or different drivers
            Schema::table('users', function (Blueprint $table) {
                $table->text('avatar')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE `users` MODIFY `avatar` VARCHAR(255) NULL");
        } catch (\Throwable $e) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('avatar', 255)->nullable()->change();
            });
        }
    }
};
