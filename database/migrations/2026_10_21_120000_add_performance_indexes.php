<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('attendance_records')) {
            Schema::table('attendance_records', function (Blueprint $table) {
                // date index allows fast monthly & daily queries without full-table scan
                $table->index('date', 'idx_att_date');
                $table->index(['date', 'status'], 'idx_att_date_status');
            });
        }

        if (Schema::hasTable('leave_requests')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->index('status', 'idx_leave_status');
                $table->index(['status', 'stage_expires_at'], 'idx_leave_status_expires');
                $table->index(['employee_id', 'status'], 'idx_leave_emp_status');
            });
        }

        if (Schema::hasTable('payslips')) {
            Schema::table('payslips', function (Blueprint $table) {
                $table->index(['period_id', 'employee_id'], 'idx_payslips_period_emp');
            });
        }

        if (Schema::hasTable('staff_requests')) {
            Schema::table('staff_requests', function (Blueprint $table) {
                $table->index('status', 'idx_staff_req_status');
                $table->index(['employee_id', 'status'], 'idx_staff_req_emp_status');
                $table->index(['type', 'status'], 'idx_staff_req_type_status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attendance_records')) {
            Schema::table('attendance_records', function (Blueprint $table) {
                $table->dropIndex('idx_att_date');
                $table->dropIndex('idx_att_date_status');
            });
        }

        if (Schema::hasTable('leave_requests')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->dropIndex('idx_leave_status');
                $table->dropIndex('idx_leave_status_expires');
                $table->dropIndex('idx_leave_emp_status');
            });
        }

        if (Schema::hasTable('payslips')) {
            Schema::table('payslips', function (Blueprint $table) {
                $table->dropIndex('idx_payslips_period_emp');
            });
        }

        if (Schema::hasTable('staff_requests')) {
            Schema::table('staff_requests', function (Blueprint $table) {
                $table->dropIndex('idx_staff_req_status');
                $table->dropIndex('idx_staff_req_emp_status');
                $table->dropIndex('idx_staff_req_type_status');
            });
        }
    }
};
