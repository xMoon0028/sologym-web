<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. Aug 6-20, 2026
            $table->date('start_date');
            $table->date('end_date');
            $table->date('date_of_payment')->nullable();
            $table->enum('status', ['draft', 'under_review', 'finalized', 'released'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('payroll_periods'); }
};
