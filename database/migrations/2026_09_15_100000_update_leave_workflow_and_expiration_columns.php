<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_requests', 'stage_expires_at')) {
                $table->timestamp('stage_expires_at')->nullable()->after('current_stage');
            }
            if (!Schema::hasColumn('leave_requests', 'expired_at')) {
                $table->timestamp('expired_at')->nullable()->after('stage_expires_at');
            }
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE leave_requests MODIFY COLUMN status VARCHAR(64) NOT NULL DEFAULT 'pending_section_head'");
            DB::statement("ALTER TABLE leave_approvals MODIFY COLUMN stage VARCHAR(64) NOT NULL");
            DB::statement("ALTER TABLE leave_approvals MODIFY COLUMN action VARCHAR(64) NOT NULL");
        }
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('leave_requests', 'stage_expires_at')) {
                $cols[] = 'stage_expires_at';
            }
            if (Schema::hasColumn('leave_requests', 'expired_at')) {
                $cols[] = 'expired_at';
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
