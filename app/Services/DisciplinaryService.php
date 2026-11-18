<?php

namespace App\Services;

use App\Models\DisciplinaryNotice;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DisciplinaryService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Issue a formal Code of Conduct / Disciplinary Notice to an employee.
     */
    public function createNotice(
        array $data,
        ?User $issuer = null,
        ?UploadedFile $incidentReport = null,
        ?UploadedFile $documentaryEvidence = null
    ): DisciplinaryNotice {
        return DB::transaction(function () use ($data, $issuer, $incidentReport, $documentaryEvidence) {
            $employee = Employee::findOrFail($data['employee_id']);

            $sanctionType = $data['sanction_type'] ?? 'written_warning';
            $categoryCode = $data['category_code'] ?? 'others';

            // In stern warning, it will need a Notice of Explanation.
            // Also for "Others" in special cases, or if explicitly requested.
            $requiresExplanation = !empty($data['requires_explanation'])
                || $sanctionType === 'stern_warning'
                || $sanctionType === 'notice_to_explain'
                || $categoryCode === 'others';

            $sanctionLabel = match ($sanctionType) {
                'written_warning' => 'Written Warning (WW)',
                'stern_warning' => 'Stern Warning (SW) - Notice to Explain Required',
                'suspension' => 'Suspension (SU)',
                'dismissal' => 'Dismissal (DI)',
                'notice_to_explain' => 'Notice to Explain (NTE)',
                'counseling' => 'Verbal Counseling / Reprimand',
                default => ucfirst(str_replace('_', ' ', $sanctionType)),
            };

            // Calculate 2 days compliance deadline per Code of Conduct section b.ii
            $deadline = null;
            if ($requiresExplanation) {
                $deadline = !empty($data['explanation_deadline'])
                    ? Carbon::parse($data['explanation_deadline'])
                    : Carbon::now()->addDays(2)->endOfDay();
            }

            // Generate sequence number
            $noticeNo = $this->generateNoticeNo($requiresExplanation);

            // Separate file uploads: Incident Report (Required) and Documentary Evidence (Optional)
            // Stored in private disks / confidential to HR/Admin
            $incidentReportPath = null;
            if ($incidentReport) {
                $incidentReportPath = $incidentReport->store('disciplinary/incident-reports', 'public');
            }

            $documentaryEvidencePath = null;
            if ($documentaryEvidence) {
                $documentaryEvidencePath = $documentaryEvidence->store('disciplinary/evidence', 'public');
            }

            $classification = $data['classification'] ?? 'minor';
            if ($categoryCode === 'others') {
                $classification = 'special';
            }

            $offenseTitle = trim($data['offense_title'] ?? '');
            if (empty($offenseTitle) && !empty($data['custom_offense_title'])) {
                $offenseTitle = trim($data['custom_offense_title']);
            }

            $categoryName = $data['category_name'] ?? 'General Code of Conduct';
            if ($categoryCode === 'others') {
                $categoryName = 'Others (Special Case)';
            }

            $notice = DisciplinaryNotice::create([
                'notice_no' => $noticeNo,
                'employee_id' => $employee->id,
                'issuer_id' => $issuer?->id ?? auth()->id(),
                'category_code' => $categoryCode,
                'category_name' => $categoryName,
                'classification' => $classification,
                'offense_title' => $offenseTitle,
                'offense_occurrence' => $data['offense_occurrence'] ?? '1st',
                'sanction_type' => $sanctionType,
                'sanction_label' => $sanctionLabel,
                'requires_explanation' => $requiresExplanation,
                'explanation_deadline' => $deadline,
                'incident_date' => Carbon::parse($data['incident_date']),
                'incident_time' => $data['incident_time'] ?? null,
                'incident_location' => $data['incident_location'] ?? null,
                'description' => $data['description'],
                'hr_clarification_note' => $data['hr_clarification_note'] ?? 'Please see the HR Officer for clarification.',
                'attachment_path' => $incidentReportPath, // Backwards compatibility
                'incident_report_path' => $incidentReportPath,
                'documentary_evidence_path' => $documentaryEvidencePath,
                'status' => 'issued',
            ]);

            // Dispatch in-app notification & send email to staff (without confidential attachments)
            try {
                $this->notificationService->notifyDisciplinaryNotice($notice);
            } catch (\Throwable $e) {
                Log::error("Error dispatching disciplinary notice notification: " . $e->getMessage());
            }

            return $notice;
        });
    }

    /**
     * HR/Admin attaches the scanned copy of physical NTE submitted by staff and processes the case.
     * This immediately clears the time-in restriction for the employee.
     */
    public function attachScannedNte(
        DisciplinaryNotice $notice,
        UploadedFile $scannedNte,
        ?string $remarks,
        User $processor
    ): DisciplinaryNotice {
        $scannedPath = $scannedNte->store('disciplinary/scanned-nte', 'public');

        $notes = $notice->hr_resolution_notes;
        if (!empty($remarks)) {
            $notes = $notes ? ($notes . "\n[NTE Processed]: " . trim($remarks)) : trim($remarks);
        }

        $notice->update([
            'scanned_nte_path' => $scannedPath,
            'nte_received_at' => Carbon::now(),
            'nte_processed_by' => $processor->id,
            'status' => in_array($notice->status, ['resolved', 'closed']) ? $notice->status : 'under_review',
            'hr_resolution_notes' => $notes,
        ]);

        // Notify staff that NTE has been received and time-in is cleared
        try {
            $this->notificationService->notifyNteProcessed($notice);
        } catch (\Throwable $e) {
            Log::error("Error notifying employee of processed NTE: " . $e->getMessage());
        }

        return $notice;
    }

    /**
     * Employee submits formal written explanation & optional supporting documentation.
     */
    public function submitExplanation(DisciplinaryNotice $notice, string $explanation, ?UploadedFile $attachment = null): DisciplinaryNotice
    {
        $employeeAttachmentPath = $notice->employee_attachment_path;
        if ($attachment) {
            $employeeAttachmentPath = $attachment->store('disciplinary-explanations', 'public');
        }

        $notice->update([
            'employee_explanation' => $explanation,
            'employee_attachment_path' => $employeeAttachmentPath,
            'explanation_submitted_at' => Carbon::now(),
            'status' => 'explanation_submitted',
        ]);

        // Notify HR team
        try {
            $this->notificationService->notifyExplanationSubmitted($notice);
        } catch (\Throwable $e) {
            Log::error("Error notifying HR of submitted explanation: " . $e->getMessage());
        }

        return $notice;
    }

    /**
     * HR resolves or closes the disciplinary notice.
     */
    public function resolveNotice(DisciplinaryNotice $notice, array $data, User $resolver): DisciplinaryNotice
    {
        $notice->update([
            'hr_resolution_notes' => $data['hr_resolution_notes'] ?? null,
            'final_sanction' => $data['final_sanction'] ?? $notice->sanction_label,
            'status' => $data['status'] ?? 'resolved',
            'resolved_by' => $resolver->id,
            'resolved_at' => Carbon::now(),
        ]);

        return $notice;
    }

    /**
     * Mark notice as viewed when the employee opens it.
     */
    public function markAsViewed(DisciplinaryNotice $notice): void
    {
        if ($notice->viewed_at === null) {
            $notice->update(['viewed_at' => Carbon::now()]);
        }
    }

    /**
     * Generate structured Notice Reference Number.
     */
    protected function generateNoticeNo(bool $isNte): string
    {
        $prefix = $isNte ? 'NTE' : 'DN';
        $year = date('Y');

        $latest = DisciplinaryNotice::where('notice_no', 'like', "{$prefix}-{$year}-%")
            ->latest('id')
            ->first();

        $seq = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest->notice_no, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $year, $seq);
    }
}
