<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('period_id')->constrained('payroll_periods')->cascadeOnDelete();
            // Rates
            $table->decimal('per_day_rate', 10, 4)->default(0);
            $table->decimal('per_hour_rate', 10, 4)->default(0);
            $table->decimal('per_minute_rate', 10, 4)->default(0);
            // Earnings
            $table->decimal('salary_and_allowances', 12, 2)->default(0);
            $table->decimal('holiday_pay', 12, 2)->default(0);
            // Deductions
            $table->decimal('sss_deduction', 10, 2)->default(0);
            $table->decimal('ph_deduction', 10, 2)->default(0);
            $table->decimal('hdmf_deduction', 10, 2)->default(0);
            $table->unsignedInteger('absent_days')->default(0);
            $table->decimal('absences_deduction', 10, 2)->default(0);
            $table->unsignedInteger('late_minutes')->default(0);
            $table->decimal('late_deduction', 10, 2)->default(0);
            $table->unsignedInteger('undertime_minutes')->default(0);
            $table->decimal('undertime_deduction', 10, 2)->default(0);
            // Totals
            $table->decimal('gross_earnings', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);
            // Status
            $table->boolean('is_finalized')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('payslips'); }
};
