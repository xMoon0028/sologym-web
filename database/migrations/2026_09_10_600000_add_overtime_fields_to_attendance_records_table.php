<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->unsignedInteger('overtime_minutes')->default(0)->after('undertime_minutes');
            $table->enum('overtime_status', ['none', 'pending', 'approved', 'rejected'])->default('none')->after('overtime_minutes');
            $table->foreignId('overtime_approved_by')->nullable()->after('overtime_status')->constrained('users')->nullOnDelete();
            $table->timestamp('overtime_approved_at')->nullable()->after('overtime_approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropForeign(['overtime_approved_by']);
            $table->dropColumn(['overtime_minutes', 'overtime_status', 'overtime_approved_by', 'overtime_approved_at']);
        });
    }
};
