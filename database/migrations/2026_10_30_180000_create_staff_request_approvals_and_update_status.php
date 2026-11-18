<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. In MySQL, alter status column from ENUM to VARCHAR(50) to support multi-stage approval statuses
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE staff_requests MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'pending'");
        }

        // 2. Add current_stage and approved_at if not present
        Schema::table('staff_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('staff_requests', 'current_stage')) {
                $table->string('current_stage', 50)->nullable()->after('status');
            }
            if (!Schema::hasColumn('staff_requests', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('reviewed_at');
            }
        });

        // 3. Create staff_request_approvals audit trail table
        if (!Schema::hasTable('staff_request_approvals')) {
            Schema::create('staff_request_approvals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('staff_request_id')->constrained('staff_requests')->cascadeOnDelete();
                $table->string('stage', 50); // section_head, executive_office, executive_director, hr
                $table->string('action', 50); // submitted, approved, recommended, rejected
                $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('approver_name')->nullable();
                $table->string('approver_role')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_request_approvals');

        Schema::table('staff_requests', function (Blueprint $table) {
            if (Schema::hasColumn('staff_requests', 'current_stage')) {
                $table->dropColumn('current_stage');
            }
            if (Schema::hasColumn('staff_requests', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
        });
    }
};
