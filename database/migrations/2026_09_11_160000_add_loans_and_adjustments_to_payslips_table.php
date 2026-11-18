<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('payslips', function (Blueprint $table) {
            $table->decimal('loan_deduction', 10, 2)->default(0)->after('undertime_deduction');
            $table->decimal('other_earnings', 10, 2)->default(0)->after('holiday_pay');
            $table->decimal('other_deductions', 10, 2)->default(0)->after('loan_deduction');
            $table->unsignedInteger('official_business_days')->default(0)->after('absent_days');
            $table->decimal('leave_days', 4, 1)->default(0)->after('official_business_days');
        });
    }

    public function down(): void {
        Schema::table('payslips', function (Blueprint $table) {
            $table->dropColumn([
                'loan_deduction',
                'other_earnings',
                'other_deductions',
                'official_business_days',
                'leave_days',
            ]);
        });
    }
};
