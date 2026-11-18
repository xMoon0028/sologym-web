<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('employee_no')->unique()->nullable();
            $table->string('name');
            $table->string('position')->nullable();
            $table->string('salary_grade')->nullable();
            $table->string('employment_status')->nullable(); // regular, probationary, contractual
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->date('date_hired')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('sex')->nullable(); // Male, Female
            $table->string('civil_status')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('employees'); }
};
