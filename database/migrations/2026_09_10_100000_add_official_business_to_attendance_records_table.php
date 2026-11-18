<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE attendance_records MODIFY COLUMN status ENUM('present', 'absent', 'late', 'half_day', 'holiday', 'leave', 'official_business', 'weekend') NOT NULL DEFAULT 'present'");
        } else {
            Schema::table('attendance_records', function (Blueprint $table) {
                $table->string('status', 30)->default('present')->change();
            });
        }
    }

    public function down(): void {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE attendance_records MODIFY COLUMN status ENUM('present', 'absent', 'late', 'half_day', 'holiday', 'leave', 'weekend') NOT NULL DEFAULT 'present'");
        }
    }
};
