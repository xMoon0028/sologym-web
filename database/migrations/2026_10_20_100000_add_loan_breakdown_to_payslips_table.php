<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('payslips', function (Blueprint $table) {
            $table->decimal('sss_loan_deduction', 10, 2)->default(0)->after('loan_deduction');
            $table->decimal('pagibig_loan_deduction', 10, 2)->default(0)->after('sss_loan_deduction');
            $table->decimal('salary_loan_deduction', 10, 2)->default(0)->after('pagibig_loan_deduction');
        });
    }

    public function down(): void {
        Schema::table('payslips', function (Blueprint $table) {
            $table->dropColumn([
                'sss_loan_deduction',
                'pagibig_loan_deduction',
                'salary_loan_deduction',
            ]);
        });
    }
};
