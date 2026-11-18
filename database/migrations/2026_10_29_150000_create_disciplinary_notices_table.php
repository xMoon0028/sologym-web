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
        if (!Schema::hasTable('disciplinary_notices')) {
            Schema::create('disciplinary_notices', function (Blueprint $table) {
                $table->id();
                $table->string('notice_no')->unique(); // e.g. DN-2026-0001 or NTE-2026-0001
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->foreignId('issuer_id')->nullable()->constrained('users')->nullOnDelete();

                // Categorization based on LASAC Code of Conduct
                $table->string('category_code', 64); // housekeeping, attendance, punctuality, etc. or others
                $table->string('category_name'); // e.g. Attendance, Serious Misbehavior, Others
                $table->string('classification', 32)->default('minor'); // minor, major, special
                $table->text('offense_title'); // Specific offense from code or custom description
                $table->string('offense_occurrence', 32)->default('1st'); // 1st, 2nd, 3rd, 4th, etc.

                // Sanctions: written_warning (WW), stern_warning (SW), suspension (SU), dismissal (DI), notice_to_explain (NTE), counseling, others
                $table->string('sanction_type', 64)->default('written_warning');
                $table->string('sanction_label')->default('Written Warning (WW)');

                // Notice to Explain requirements
                $table->boolean('requires_explanation')->default(false);
                $table->date('explanation_deadline')->nullable(); // 2 days from issuance per policy

                // Incident Details
                $table->date('incident_date');
                $table->string('incident_time', 32)->nullable();
                $table->string('incident_location')->nullable();
                $table->text('description'); // Facts and specifications
                $table->string('hr_clarification_note')->default('Please see the HR Officer for clarification.');
                $table->string('attachment_path')->nullable(); // HR incident report or evidence

                // Status & Staff Explanation
                $table->string('status', 32)->default('issued'); // issued, explanation_submitted, under_review, resolved, closed
                $table->text('employee_explanation')->nullable();
                $table->string('employee_attachment_path')->nullable();
                $table->timestamp('explanation_submitted_at')->nullable();
                $table->timestamp('viewed_at')->nullable();

                // Resolution
                $table->text('hr_resolution_notes')->nullable();
                $table->string('final_sanction')->nullable();
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disciplinary_notices');
    }
};
