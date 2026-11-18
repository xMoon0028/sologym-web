<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1. Add approval_role and leave credits to employees table if not present
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'approval_role')) {
                $table->string('approval_role')->default('none')->after('is_active'); // none, section_head, hr_officer, executive_office
            }
            if (!Schema::hasColumn('employees', 'vacation_leave_credits')) {
                $table->decimal('vacation_leave_credits', 5, 1)->default(15.0)->after('approval_role');
            }
            if (!Schema::hasColumn('employees', 'sick_leave_credits')) {
                $table->decimal('sick_leave_credits', 5, 1)->default(15.0)->after('vacation_leave_credits');
            }
            if (!Schema::hasColumn('employees', 'emergency_leave_credits')) {
                $table->decimal('emergency_leave_credits', 5, 1)->default(3.0)->after('sick_leave_credits');
            }
        });

        // 2. Create leave_requests table
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->enum('leave_type', ['vacation', 'sick', 'offset', 'emergency']);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days_count', 4, 1)->default(1.0);
            $table->boolean('is_half_day')->default(false);
            $table->string('half_day_type')->nullable(); // morning, afternoon
            $table->text('reason');
            $table->string('attachment')->nullable();
            $table->string('status')->default('pending_section_head');
            $table->string('current_stage')->default('section_head'); // section_head, executive_office, executive_director, completed
            $table->timestamp('stage_expires_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // 3. Create leave_approvals table for multi-tier approval notes and tracking history
        Schema::create('leave_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')->constrained('leave_requests')->cascadeOnDelete();
            $table->string('stage'); // section_head, executive_office, executive_director
            $table->string('action'); // approved, recommended, rejected, returned, expired
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approver_name');
            $table->string('approver_role'); // e.g. "Section Head", "Executive Office", "Executive Director", "Admin"
            $table->text('notes')->nullable(); // optional approval / rejection notes
            $table->timestamps();
        });

        // 4. Create app_notifications table for real-time notification bell
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type'); // leave_submitted, leave_section_approved, leave_hr_recommended, leave_approved, leave_rejected
            $table->string('title');
            $table->text('message');
            $table->string('link')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('leave_approvals');
        Schema::dropIfExists('leave_requests');

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'approval_role',
                'vacation_leave_credits',
                'sick_leave_credits',
                'emergency_leave_credits',
            ]);
        });
    }
};
