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
        Schema::create('employee_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('loan_type'); // sss, pagibig, salary, emergency, other
            $table->string('loan_name'); // e.g., 'SSS Salary Loan', 'Pag-IBIG Multi-Purpose Loan'
            $table->string('reference_no')->nullable(); // SSS or Pag-IBIG loan/application number
            $table->decimal('principal_amount', 12, 2)->default(0);
            $table->decimal('interest_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0); // principal + interest (total to pay)
            $table->decimal('monthly_amortization', 10, 2)->default(0);
            $table->decimal('amortization_per_cutoff', 10, 2)->default(0); // per payroll deduction
            $table->string('deduction_schedule')->default('both'); // both, mid_month, end_month
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('remaining_balance', 12, 2)->default(0);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('active'); // active, completed, paused, cancelled
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_loan_id')->constrained('employee_loans')->cascadeOnDelete();
            $table->foreignId('payslip_id')->nullable()->constrained('payslips')->nullOnDelete();
            $table->foreignId('payroll_period_id')->nullable()->constrained('payroll_periods')->nullOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 10, 2);
            $table->string('payment_type')->default('payroll_deduction'); // payroll_deduction, manual_payment, adjustment
            $table->string('reference_no')->nullable(); // receipt # or payment ref
            $table->text('remarks')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_payments');
        Schema::dropIfExists('employee_loans');
    }
};
