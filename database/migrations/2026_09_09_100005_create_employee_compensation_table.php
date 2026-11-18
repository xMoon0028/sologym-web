<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('employee_compensation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->decimal('basic_salary_80', 12, 2)->default(0); // 80% basic
            $table->decimal('allowance_20', 12, 2)->default(0);    // 20% allowance
            $table->decimal('total_compensation', 12, 2)->default(0);
            $table->decimal('sss_deduction', 10, 2)->default(0);
            $table->decimal('ph_deduction', 10, 2)->default(0);    // PhilHealth
            $table->decimal('pi_deduction', 10, 2)->default(0);    // Pag-IBIG
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('employee_compensation'); }
};
