<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->decimal('hours_worked', 5, 2)->default(0);
            $table->unsignedInteger('late_minutes')->default(0);
            $table->unsignedInteger('undertime_minutes')->default(0);
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'holiday', 'leave', 'official_business', 'weekend'])->default('present');
            $table->string('source')->default('manual'); // manual, import
            $table->timestamps();
            $table->unique(['employee_id', 'date']);
        });
    }
    public function down(): void { Schema::dropIfExists('attendance_records'); }
};
