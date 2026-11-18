<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('time_credit_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->enum('type', ['accrual', 'deduction', 'adjustment']); // accrual=earned, deduction=used, adjustment=manual
            $table->decimal('hours', 5, 2)->default(0); // positive for accrual, negative for deduction
            $table->string('reason')->nullable(); // e.g. 'Weekday overtime', 'Weekend work', 'Offset leave used'
            $table->decimal('running_balance', 8, 2)->default(0); // balance after this entry
            $table->foreignId('attendance_record_id')->nullable()->constrained('attendance_records')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('time_credit_ledger'); }
};
