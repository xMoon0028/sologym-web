<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('disciplinary_notices', function (Blueprint $table) {
            if (!Schema::hasColumn('disciplinary_notices', 'incident_report_path')) {
                $table->string('incident_report_path')->nullable()->after('attachment_path');
            }
            if (!Schema::hasColumn('disciplinary_notices', 'documentary_evidence_path')) {
                $table->string('documentary_evidence_path')->nullable()->after('incident_report_path');
            }
            if (!Schema::hasColumn('disciplinary_notices', 'scanned_nte_path')) {
                $table->string('scanned_nte_path')->nullable()->after('documentary_evidence_path');
            }
            if (!Schema::hasColumn('disciplinary_notices', 'nte_received_at')) {
                $table->timestamp('nte_received_at')->nullable()->after('scanned_nte_path');
            }
            if (!Schema::hasColumn('disciplinary_notices', 'nte_processed_by')) {
                $table->foreignId('nte_processed_by')->nullable()->constrained('users')->nullOnDelete()->after('nte_received_at');
            }
        });

        // Backfill existing attachment_path into incident_report_path if present
        try {
            DB::table('disciplinary_notices')
                ->whereNotNull('attachment_path')
                ->whereNull('incident_report_path')
                ->update(['incident_report_path' => DB::raw('attachment_path')]);
        } catch (\Throwable $e) {
            // Ignore if in dry-run/mock
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('disciplinary_notices', function (Blueprint $table) {
            if (Schema::hasColumn('disciplinary_notices', 'nte_processed_by')) {
                $table->dropForeign(['nte_processed_by']);
                $table->dropColumn('nte_processed_by');
            }
            $cols = array_filter(['incident_report_path', 'documentary_evidence_path', 'scanned_nte_path', 'nte_received_at'], function($c) {
                return Schema::hasColumn('disciplinary_notices', $c);
            });
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
