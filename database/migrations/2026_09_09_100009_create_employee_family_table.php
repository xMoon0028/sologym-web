<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('employee_family', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            // Marriage type
            $table->string('marriage_type')->nullable(); // civil, church, live_in, single
            $table->string('spouse_occupation')->nullable();
            $table->unsignedInteger('no_of_siblings')->default(0);
            $table->unsignedInteger('no_of_children')->default(0);
            $table->text('dependents')->nullable();
            // Single sub-status
            $table->string('single_status')->nullable(); // student, non_student
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('employee_family'); }
};
