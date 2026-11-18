<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->enum('type', ['seminar', 'document']);
            $table->enum('status', ['pending', 'approved', 'report_pending', 'completed', 'rejected'])->default('pending');

            // Document Request Details
            $table->string('document_type')->nullable(); // COE, Service Record, Compensation, Clearance, etc.
            $table->text('purpose')->nullable();
            $table->date('date_needed')->nullable();
            $table->string('document_file_path')->nullable(); // Uploaded document copy by HR for employee

            // Seminar / Training Request Details
            $table->string('seminar_title')->nullable();
            $table->string('organizer')->nullable();
            $table->string('venue')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('estimated_budget', 12, 2)->default(0);
            $table->string('invitation_file_path')->nullable();
            $table->text('justification')->nullable();

            // Seminar Report (Mandatory Post-Approval Step for Seminars)
            $table->timestamp('report_submitted_at')->nullable();
            $table->text('report_summary')->nullable(); // Key learnings & insights
            $table->text('report_action_plan')->nullable(); // Application / Re-echo plan
            $table->string('certificate_file_path')->nullable(); // Certificate of completion
            $table->string('additional_materials_path')->nullable(); // Presentation / Handouts
            $table->enum('report_status', ['pending_review', 'acknowledged'])->nullable();
            $table->foreignId('report_acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('report_acknowledged_at')->nullable();

            // HR / Admin Review Details
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_requests');
    }
};
