<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('health_assistance_funds')) {
            Schema::create('health_assistance_funds', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->string('member_number', 50)->nullable()->index();
                $table->string('plan_year', 30)->nullable();
                $table->decimal('contribution_amount', 12, 2)->default(0); // Voluntary staff contribution
                $table->decimal('coverage_amount', 12, 2)->default(0);     // Total HMO / benefit limit
                $table->decimal('amount_used', 12, 2)->default(0);         // Total claims disbursed
                $table->decimal('remaining_balance', 12, 2)->default(0);   // Coverage - amount_used
                $table->date('start_date');                                // Effective date
                $table->date('end_date');                                  // Expiration date (365 days)
                $table->unsignedInteger('validity_days')->default(365);
                $table->string('payment_method', 50)->default('voluntary_contribution'); // payroll_deduction, direct_cash, bank_transfer
                $table->string('receipt_no', 100)->nullable();
                $table->string('receipt_file_path')->nullable();
                $table->string('status', 30)->default('active');           // active, expiring_soon, expired, cancelled
                $table->timestamp('expiration_notified_at')->nullable();   // When 30-day notice was sent
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['employee_id', 'status']);
                $table->index(['end_date', 'status']);
            });
        }

        if (!Schema::hasTable('health_assistance_claims')) {
            Schema::create('health_assistance_claims', function (Blueprint $table) {
                $table->id();
                $table->foreignId('health_assistance_fund_id')->constrained('health_assistance_funds')->cascadeOnDelete();
                $table->string('claim_no', 50)->nullable()->index();
                $table->date('claim_date');
                $table->string('claim_type', 50)->default('outpatient'); // hospitalization, outpatient, laboratory, medicine, dental, emergency, other
                $table->string('healthcare_provider')->nullable();       // Hospital / Clinic / Diagnostic Center
                $table->text('diagnosis_reason');                        // Medical indication / illness
                $table->decimal('amount_requested', 12, 2)->default(0);
                $table->decimal('amount_approved', 12, 2)->default(0);
                $table->string('status', 30)->default('approved');       // approved, pending, rejected
                $table->string('receipt_file_path')->nullable();         // Scanned medical receipt / prescription / billing
                $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index(['health_assistance_fund_id', 'claim_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('health_assistance_claims');
        Schema::dropIfExists('health_assistance_funds');
    }
};
