<?php

namespace App\Services;

use App\Mail\StaffNotificationMail;
use App\Models\AppNotification;
use App\Models\AttendanceRecord;
use App\Models\CertificateTraining;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\HealthAssistanceFund;
use App\Models\TimeCreditLedger;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send in-app notification to a user, and also send an email with redirect button and optional attachments.
     */
    /**
     * Send in-app notification to a user, and also send an email with redirect button and optional attachments.
     */
    public function notifyUser(User|int $user, string $type, string $title, string $message, ?string $link = null, array $attachments = []): AppNotification
    {
        $userId = $user instanceof User ? $user->id : $user;
        $userModel = $user instanceof User ? $user : User::find($userId);

        $notification = AppNotification::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'link'    => $link,
            'is_read' => false,
        ]);

        if (config('hris.email_notifications', true) && $userModel) {
            $email = $userModel->email;
            if (empty($email) && $userModel->employee && !empty($userModel->employee->email)) {
                $email = $userModel->employee->email;
            }

            if (!empty($email)) {
                $this->sendNotificationEmail($userModel, $type, $title, $message, $link, $attachments, $email);
            }
        }

        return $notification;
    }

    /**
     * Notify an employee of their new released payslip and attach an official PDF copy.
     */
    public function notifyPayslipReleased(Payslip $payslip, bool $force = false): ?AppNotification
    {
        $employee = $payslip->employee;
        if (!$employee) {
            return null;
        }

        $user = $employee->user ?: ($employee->email ? User::where('email', $employee->email)->first() : null);

        $period = $payslip->period;
        $periodName = $period ? $period->name : 'Recent Period';
        $title = "💰 New Payslip Available: {$periodName}";

        if ($user && !$force) {
            // Prevent duplicate notification for the exact same payslip & period unless forced
            $exists = AppNotification::where('user_id', $user->id)
                ->where('type', 'payslip_released')
                ->where('title', $title)
                ->exists();

            if ($exists) {
                return null;
            }
        }

        $netPayFormatted = number_format((float) $payslip->net_pay, 2);
        $message = "Your official payslip for {$periodName} is now available in your LASAC HRIS account. Net Take-Home Pay: ₱{$netPayFormatted}.\n\nAn official PDF copy of your payslip is attached to this email for your records, and you can also view or download it anytime in the HRIS Employee Portal.";
        $link = route('portal.payslips.index');

        $targetEmail = $user?->email ?: $employee->email;

        // Generate and attach PDF copy ONLY if sending an email
        $attachments = [];
        if (!empty($targetEmail) && config('hris.email_notifications', true)) {
            try {
                $pdfData = $this->generatePayslipPdfData($payslip);
                $empNo = $employee->employee_no ?? ('ID-' . $employee->id);
                $periodSlug = \Illuminate\Support\Str::slug($periodName);
                $filename = "payslip-{$empNo}-{$periodSlug}.pdf";
                $attachments[] = [
                    'data' => $pdfData,
                    'name' => $filename,
                    'options' => ['mime' => 'application/pdf'],
                ];
            } catch (\Throwable $e) {
                Log::warning("Could not generate PDF attachment for payslip {$payslip->id}: " . $e->getMessage());
            }
        }

        if ($user) {
            if (empty($user->email) && !empty($employee->email)) {
                $user->email = $employee->email;
            }
            return $this->notifyUser($user, 'payslip_released', $title, $message, $link, $attachments);
        }

        if (!empty($employee->email) && config('hris.email_notifications', true)) {
            try {
                Mail::mailer('smtp')->to($employee->email)->send(new StaffNotificationMail(
                    $title,
                    $message,
                    url($link),
                    'View Payslip in HRIS',
                    $employee->name ?: 'Staff Member',
                    $attachments
                ));
            } catch (\Throwable $e) {
                Log::error("Failed to dispatch payslip email to {$employee->email}: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Release notifications to all employees who have a payslip in the period.
     */
    public function notifyPayrollPeriodReleased(PayrollPeriod $period, bool $force = false): int
    {
        $period->load(['payslips.employee.user']);
        $count = 0;

        foreach ($period->payslips as $payslip) {
            if ($this->notifyPayslipReleased($payslip, $force)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Scan for scheduled payroll periods whose release date/time has passed and dispatch notifications.
     */
    public function checkAndDispatchScheduledReleases(): int
    {
        if (\Illuminate\Support\Facades\Cache::has('scheduled_releases_dispatched')) {
            return 0;
        }
        \Illuminate\Support\Facades\Cache::put('scheduled_releases_dispatched', true, now()->addMinutes(30));

        $periods = PayrollPeriod::where('status', 'released')
            ->whereNotNull('released_at')
            ->where('released_at', '<=', now())
            ->where('released_at', '>=', now()->subHours(24))
            ->with(['payslips.employee.user'])
            ->get();

        $totalSent = 0;
        foreach ($periods as $period) {
            $totalSent += $this->notifyPayrollPeriodReleased($period);
        }

        return $totalSent;
    }

    /**
     * Notify staff when overtime/weekend duty time credits are approved.
     */
    public function notifyTimeCreditApproved(TimeCreditLedger $entry): ?AppNotification
    {
        $employee = $entry->employee;
        if (!$employee || !$employee->user) {
            return null;
        }

        $hours = number_format((float) $entry->hours, 2);
        $balance = number_format((float) $entry->running_balance, 2);
        $dateStr = $entry->date ? $entry->date->format('M d, Y') : 'Attendance record';

        $title = "⏱️ Time Credit Approved (+{$hours} hrs)";
        $message = "Your overtime/duty credits for {$dateStr} ({$hours} hrs) have been approved by HR/Admin. New balance: {$balance} hrs.";
        $link = route('portal.time-credits.index');

        return $this->notifyUser($employee->user, 'time_credit_approved', $title, $message, $link);
    }

    /**
     * Notify staff when overtime credits are rejected.
     */
    public function notifyTimeCreditRejected(TimeCreditLedger $entry, ?string $reason = null): ?AppNotification
    {
        $employee = $entry->employee;
        if (!$employee || !$employee->user) {
            return null;
        }

        $dateStr = $entry->date ? $entry->date->format('M d, Y') : 'Attendance record';
        $title = "⚠️ Overtime Credit Disapproved";
        $reasonText = !empty($reason) ? " Reason: \"{$reason}\"" : " Reason: Not eligible under guidelines.";
        $message = "Your overtime credit request for {$dateStr} ({$entry->hours} hrs) was not approved.{$reasonText}";
        $link = route('portal.time-credits.index');

        return $this->notifyUser($employee->user, 'time_credit_rejected', $title, $message, $link);
    }

    /**
     * Notify staff of a manual time credit adjustment.
     */
    public function notifyTimeCreditAdjustment(Employee $employee, float $hours, string $reason, float $newBalance): ?AppNotification
    {
        if (!$employee->user) {
            return null;
        }

        $sign = $hours >= 0 ? "+{$hours}" : "{$hours}";
        $balanceStr = number_format($newBalance, 2);
        $title = "⏱️ Time Credit Balance Adjusted ({$sign} hrs)";
        $message = "HR adjusted your time credit balance by {$sign} hrs. Reason: \"{$reason}\". Current balance: {$balanceStr} hrs.";
        $link = route('portal.time-credits.index');

        return $this->notifyUser($employee->user, 'time_credit_adjusted', $title, $message, $link);
    }

    /**
     * Notify staff when HR updates attendance status, hours, or time punches.
     */
    public function notifyAttendanceUpdated(AttendanceRecord $record, ?string $actionNote = null): ?AppNotification
    {
        $employee = $record->employee;
        if (!$employee || !$employee->user) {
            return null;
        }

        $dateStr = $record->date ? $record->date->format('M d, Y') : 'Attendance';
        $statusLabel = ucwords(str_replace('_', ' ', $record->status));

        $details = [];
        if ($record->hours_worked > 0) {
            $details[] = "{$record->hours_worked} hrs worked";
        }
        if ($record->late_minutes > 0) {
            $details[] = "{$record->late_minutes}m late";
        }
        if ($record->undertime_minutes > 0) {
            $details[] = "{$record->undertime_minutes}m undertime";
        }
        $detailStr = !empty($details) ? ' (' . implode(', ', $details) . ')' : '';

        $title = "📅 Attendance Record Updated ({$dateStr})";
        $message = "HR updated your attendance record for {$dateStr} to '{$statusLabel}'{$detailStr}." .
            (!empty($actionNote) ? " Notes: \"{$actionNote}\"" : "");
        $link = route('portal.attendance.index');

        return $this->notifyUser($employee->user, 'attendance_updated', $title, $message, $link);
    }

    /**
     * Notify staff when leave credits (Vacation, Sick, Emergency) are adjusted.
     */
    public function notifyLeaveCreditAdjustment(Employee $employee, string $leaveType, float $oldCredits, float $newCredits): ?AppNotification
    {
        if (!$employee->user || round($oldCredits, 2) === round($newCredits, 2)) {
            return null;
        }

        $leaveName = match ($leaveType) {
            'vacation' => 'Vacation Leave (VL)',
            'sick'     => 'Sick Leave (SL)',
            'emergency'=> 'Emergency Leave (EL)',
            default    => ucwords(str_replace('_', ' ', $leaveType)),
        };

        $diff = round($newCredits - $oldCredits, 2);
        $diffStr = ($diff > 0 ? "+{$diff}" : "{$diff}") . ' days';

        $title = "🏖️ {$leaveName} Balance Updated";
        $message = "HR adjusted your {$leaveName} balance from {$oldCredits} to {$newCredits} days ({$diffStr}).";
        $link = route('portal.leaves.index');

        return $this->notifyUser($employee->user, 'leave_credits_adjusted', $title, $message, $link);
    }

    /**
     * Notify staff when monthly compensation is adjusted.
     */
    public function notifyCompensationUpdated(Employee $employee, float $oldTotal, float $newTotal): ?AppNotification
    {
        if (!$employee->user || round($oldTotal, 2) === round($newTotal, 2)) {
            return null;
        }

        $formattedNew = number_format($newTotal, 2);
        $title = "💼 Compensation Record Updated";
        $message = "Your total monthly compensation record has been updated by HR to ₱{$formattedNew}.";
        $link = route('portal.profile');

        return $this->notifyUser($employee->user, 'compensation_updated', $title, $message, $link);
    }

    /**
     * Notify staff of an expiring certificate / training.
     */
    public function notifyCertificateExpiring(CertificateTraining $cert): ?AppNotification
    {
        $employee = $cert->employee;
        if (!$employee || !$employee->user || !$cert->expiry_date) {
            return null;
        }

        $days = (int) now()->diffInDays($cert->expiry_date, false);
        $timeText = $days > 0 ? "in {$days} days" : 'soon';
        $title = "📜 Certificate Expiring: {$cert->title}";
        $message = "Your {$cert->type} \"{$cert->title}\" is expiring {$timeText} on {$cert->expiry_date->format('M d, Y')}. Please review and submit updated credentials.";
        $link = route('portal.profile');

        return $this->notifyUser($employee->user, 'certificate_expiring', $title, $message, $link);
    }

    /**
     * Send email notification to user with resilient error handling.
     */
    /**
     * Send email notification to user with resilient error handling.
     */
    protected function sendNotificationEmail(User $user, string $type, string $title, string $message, ?string $link = null, array $attachments = [], ?string $overrideEmail = null): void
    {
        $targetEmail = $overrideEmail ?: $user->email;
        if (empty($targetEmail)) {
            return;
        }

        try {
            $actionUrl = $this->resolveActionUrl($link, $user);
            $actionText = $this->resolveActionText($type);
            $recipientName = $user->name ?: 'Staff Member';

            Mail::mailer('smtp')->to($targetEmail)->send(new StaffNotificationMail(
                $title,
                $message,
                $actionUrl,
                $actionText,
                $recipientName,
                $attachments
            ));
            Log::info("Notification email dispatched via SMTP to {$targetEmail} [{$type}]");
        } catch (\Throwable $e) {
            Log::error("Failed to dispatch notification email to {$targetEmail}: " . $e->getMessage(), [
                'type' => $type,
                'title' => $title,
                'exception' => get_class($e),
            ]);
        }
    }

    /**
     * Send a live test email to verify SMTP and network connectivity.
     */
    public function testEmailConnection(string $toEmail): array
    {
        $defaultMailer = config('mail.default');
        $fromAddress = config('mail.from.address');
        $smtpHost = config('mail.mailers.smtp.host');
        $smtpPort = config('mail.mailers.smtp.port');

        try {
            $mail = new StaffNotificationMail(
                "LASAC HRIS: Email System Test",
                "This is a test notification email from LASAC HRIS.\n\nIf you received this message, your SMTP credentials, TLS encryption, and mail dispatch settings are correctly configured and operational.",
                route('admin.dashboard'),
                'Access HRIS Dashboard',
                'HRIS Administrator'
            );

            // Force using SMTP mailer explicitly to guarantee real network transmission
            Mail::mailer('smtp')->to($toEmail)->send($mail);

            return [
                'success' => true,
                'message' => "Test email successfully delivered to Google SMTP ({$smtpHost}:{$smtpPort}) from {$fromAddress} for {$toEmail}! If not in your Inbox, please check your Spam/Junk folder, and check the 'Sent' folder of {$fromAddress}.",
            ];
        } catch (\Throwable $e) {
            Log::error("Test email connection failed: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "SMTP connection failed: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate official PDF binary data for a payslip copy.
     */
    public function generatePayslipPdfData(Payslip $payslip): string
    {
        $payslip->loadMissing(['employee.department', 'employee.compensation', 'customItems', 'period']);
        $period = $payslip->period;
        $payrollService = app(\App\Services\PayrollService::class);
        $amountInWords = $payrollService->amountInWords((float) $payslip->net_pay);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.payslip', compact('payslip', 'period', 'amountInWords'))
            ->setPaper('a5', 'portrait');

        return $pdf->output();
    }

    /**
     * Resolve absolute action URL for the email redirect button.
     */
    protected function resolveActionUrl(?string $link, User $user): string
    {
        if (!empty($link)) {
            if (str_starts_with($link, 'http://') || str_starts_with($link, 'https://')) {
                return $link;
            }
            return url($link);
        }

        // Default redirect to appropriate dashboard
        if ($user->hasRole('admin')) {
            return route('admin.dashboard');
        }

        return route('portal.dashboard');
    }

    /**
     * Resolve contextual button text based on notification type.
     */
    protected function resolveActionText(string $type): string
    {
        return match (true) {
            str_contains($type, 'payslip') => 'View Payslip in HRIS',
            str_contains($type, 'leave') => 'View Leave Application in HRIS',
            str_contains($type, 'time_credit') => 'View Time Credits in HRIS',
            str_contains($type, 'attendance') => 'View Attendance in HRIS',
            str_contains($type, 'certificate') => 'View Credentials in HRIS',
            str_contains($type, 'compensation') => 'View Profile in HRIS',
            str_contains($type, 'disciplinary') => 'View Code of Conduct Notice',
            str_contains($type, 'request') => 'View Staff Request in HRIS',
            str_contains($type, 'haf') => 'View Health Assistance Fund',
            default => 'Open LASAC HRIS',
        };
    }

    /**
     * Dispatch notification and email for official Code of Conduct / Disciplinary Notice.
     */
    public function notifyDisciplinaryNotice(\App\Models\DisciplinaryNotice $notice): ?AppNotification
    {
        $employee = $notice->employee;
        if (!$employee) {
            return null;
        }

        $user = $employee->user ?: ($employee->email ? User::where('email', $employee->email)->first() : null);
        $targetEmail = $user?->email ?: $employee->email;

        $title = "⚠️ Code of Conduct Notice: {$notice->sanction_label}";

        $deadlineStr = $notice->explanation_deadline ? $notice->explanation_deadline->format('F d, Y') : 'Within 2 days';

        $explanationText = '';
        if ($notice->requires_explanation) {
            $explanationText = "\n\n⚠️ ACTION REQUIRED: NOTICE TO EXPLAIN (NTE)\n"
                . "You are required to personally proceed to the Human Resources (HR) Department for the submission of your written Notice to Explain (NTE) within two (2) days (Deadline: {$deadlineStr}).\n"
                . "Please note: Failure to submit your written NTE to HR within 2 days will restrict your account and you will NOT be able to time in.";
        }

        $message = "You have received an official Code of Conduct notice from Human Resources.\n\n"
            . "• Notice Reference: {$notice->notice_no}\n"
            . "• Category: {$notice->category_name}\n"
            . "• Offense: {$notice->offense_title}\n"
            . "• Incident Date: {$notice->incident_date->format('F d, Y')}\n"
            . "• Sanction / Action: {$notice->sanction_label}"
            . $explanationText
            . "\n\n*** Please see the HR Officer for clarification. ***";

        $link = route('portal.conduct.show', $notice);
        $actionText = 'View Notice in HRIS Portal';

        // Internal documents (Incident Report & Evidence) are confidential to HR/Admin and never attached to staff emails
        $attachments = [];

        if ($user) {
            if (empty($user->email) && !empty($employee->email)) {
                $user->email = $employee->email;
            }
            return $this->notifyUser($user, 'disciplinary_notice', $title, $message, $link, $attachments);
        }

        if (!empty($targetEmail) && config('hris.email_notifications', true)) {
            try {
                Mail::mailer('smtp')->to($targetEmail)->send(new StaffNotificationMail(
                    $title,
                    $message,
                    url($link),
                    $actionText,
                    $employee->name ?: 'Staff Member',
                    $attachments
                ));
            } catch (\Throwable $e) {
                Log::error("Failed to dispatch disciplinary notice email to {$targetEmail}: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Notify employee when their physical NTE has been received and processed by HR, restoring time-in privileges.
     */
    public function notifyNteProcessed(\App\Models\DisciplinaryNotice $notice): ?AppNotification
    {
        $employee = $notice->employee;
        if (!$employee) {
            return null;
        }

        $user = $employee->user ?: ($employee->email ? User::where('email', $employee->email)->first() : null);
        $targetEmail = $user?->email ?: $employee->email;

        $title = "✅ Notice to Explain (NTE) Processed: {$notice->notice_no}";
        $message = "Human Resources has received, attached, and processed your Notice to Explain (NTE) regarding notice {$notice->notice_no}.\n\nYour case has been updated and your Time-In access is active.";
        $link = route('portal.conduct.show', $notice);
        $actionText = 'View Case Status';

        if ($user) {
            return $this->notifyUser($user, 'disciplinary_nte_processed', $title, $message, $link);
        }

        if (!empty($targetEmail) && config('hris.email_notifications', true)) {
            try {
                Mail::mailer('smtp')->to($targetEmail)->send(new StaffNotificationMail(
                    $title,
                    $message,
                    url($link),
                    $actionText,
                    $employee->name ?: 'Staff Member'
                ));
            } catch (\Throwable $e) {
                Log::error("Failed to dispatch NTE processed email to {$targetEmail}: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Notify HR when staff submits an explanation to an NTE.
     */
    public function notifyExplanationSubmitted(\App\Models\DisciplinaryNotice $notice): void
    {
        $employee = $notice->employee;
        $title = "📝 Explanation Submitted: {$notice->notice_no} ({$employee->name})";
        $message = "Staff member {$employee->name} has submitted their formal written explanation regarding {$notice->notice_no} ({$notice->offense_title}). Please review the notice in HR Admin.";
        $link = route('admin.disciplinary.show', $notice);

        $adminUsers = User::role('admin')->get();
        foreach ($adminUsers as $admin) {
            $this->notifyUser($admin, 'disciplinary_explanation', $title, $message, $link);
        }
    }

    /**
     * Notify staff when their Health Assistance Fund (HMO) coverage is expiring in 30 days (1 month).
     */
    public function notifyHealthAssistanceExpiring(HealthAssistanceFund $fund): ?AppNotification
    {
        $employee = $fund->employee;
        if (!$employee) {
            return null;
        }

        $user = $employee->user ?: ($employee->email ? User::where('email', $employee->email)->first() : null);
        $targetEmail = $user?->email ?: $employee->email;

        $days = $fund->days_remaining;
        $dayText = $days === 1 ? '1 day' : "{$days} days";
        $expiryDateStr = $fund->end_date ? $fund->end_date->format('F d, Y') : 'soon';
        $refNo = $fund->member_number ?: "HAF-{$fund->id}";

        $title = "Health Assistance Fund (HMO) Expiring in {$dayText}";
        $message = "Your Health Assistance Fund (HMO) coverage (Reference: {$refNo}) is scheduled to expire on {$expiryDateStr} ({$dayText} remaining).\n\n"
            . "As this is a voluntary staff contribution valid for 365 days, please coordinate with the HR/Admin Office to arrange your voluntary renewal contribution and ensure continuous healthcare assistance and benefit coverage.";

        $link = route('portal.haf.index');
        $actionText = 'View Health Assistance Fund';

        if ($user) {
            if (empty($user->email) && !empty($employee->email)) {
                $user->email = $employee->email;
            }
            return $this->notifyUser($user, 'haf_expiring', $title, $message, $link);
        }

        if (!empty($targetEmail) && config('hris.email_notifications', true)) {
            try {
                Mail::mailer('smtp')->to($targetEmail)->send(new StaffNotificationMail(
                    $title,
                    $message,
                    url($link),
                    $actionText,
                    $employee->name ?: 'Staff Member'
                ));
            } catch (\Throwable $e) {
                Log::error("Failed to dispatch HAF expiring email to {$targetEmail}: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Notify staff when enrolled or renewed into Health Assistance Fund (HMO).
     */
    public function notifyHealthAssistanceEnrolled(HealthAssistanceFund $fund): ?AppNotification
    {
        $employee = $fund->employee;
        if (!$employee) {
            return null;
        }

        $user = $employee->user ?: ($employee->email ? User::where('email', $employee->email)->first() : null);
        $targetEmail = $user?->email ?: $employee->email;

        $startStr = $fund->start_date ? $fund->start_date->format('M d, Y') : '';
        $endStr = $fund->end_date ? $fund->end_date->format('M d, Y') : '';
        $coverageStr = number_format((float) $fund->coverage_amount, 2);
        $contribStr = number_format((float) $fund->contribution_amount, 2);
        $refNo = $fund->member_number ?: "HAF-{$fund->id}";

        $title = "Health Assistance Fund (HMO) Coverage Enrolled";
        $message = "Your Health Assistance Fund (HMO) membership (Reference: {$refNo}) has been successfully enrolled/renewed by HR.\n\n"
            . "• Coverage Validity: 365 Days ({$startStr} to {$endStr})\n"
            . "• Voluntary Contribution: ₱{$contribStr}\n"
            . "• Total Health Benefit Limit: ₱{$coverageStr}\n\n"
            . "You can view your available coverage, validity period, and claims history at any time on the Staff Portal.";

        $link = route('portal.haf.index');

        if ($user) {
            if (empty($user->email) && !empty($employee->email)) {
                $user->email = $employee->email;
            }
            return $this->notifyUser($user, 'haf_enrolled', $title, $message, $link);
        }

        if (!empty($targetEmail) && config('hris.email_notifications', true)) {
            try {
                Mail::mailer('smtp')->to($targetEmail)->send(new StaffNotificationMail(
                    $title,
                    $message,
                    url($link),
                    'View Health Assistance Fund',
                    $employee->name ?: 'Staff Member'
                ));
            } catch (\Throwable $e) {
                Log::error("Failed to dispatch HAF enrolled email to {$targetEmail}: " . $e->getMessage());
            }
        }

        return null;
    }
}
