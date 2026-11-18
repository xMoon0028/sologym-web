<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('employee_iq', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('total_stanine')->nullable();
            $table->string('verbal')->nullable();
            $table->string('non_verbal')->nullable();
            $table->string('holland_personality')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('employee_iq'); }
};
