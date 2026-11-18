<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\LeaveApproval;
use App\Models\LeaveRequest;
use App\Models\TimeCreditLedger;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveService
{
    /**
     * Approval time limit in hours per stage (1 day = 24 hours).
     */
    public const STAGE_EXPIRATION_HOURS = 24;

    /**
     * Minimum advance notice in days before start_date.
     */
    public const MIN_ADVANCE_DAYS = 3;

    /**
     * Ensure leave applications and approvals occur on weekdays during working hours (8:00 AM – 5:00 PM).
     */
    public function validateWorkingHoursForLeave(string $action = 'file'): void
    {
        $now = now('Asia/Manila');
        $isWeekday = $now->isWeekday();
        $hour = (int) $now->hour;
        $minute = (int) $now->minute;
        $timeInMinutes = ($hour * 60) + $minute;

        // 8:00 AM (480 min) to 5:00 PM (1020 min)
        $isWorkingHours = ($timeInMinutes >= 480 && $timeInMinutes <= 1020);

        if (!$isWeekday || !$isWorkingHours) {
            $actionVerb = ($action === 'approve') ? 'Leave approvals and reviews' : 'Filing leave applications';
            $currentTimeStr = $now->format('l, h:i A');
            throw ValidationException::withMessages([
                'working_hours' => "{$actionVerb} are only available on weekdays (Monday to Friday) during working hours (8:00 AM – 5:00 PM). Current time: {$currentTimeStr}.",
            ]);
        }
    }

    /**
     * Compute stage expiration timestamp:
     * 1 business day (24 hours), skipping weekends.
     * If application is on Friday, weekends are not counted towards expiration,
     * advancing deadline to Monday.
     */
    public function calculateStageExpiration(?Carbon $from = null): Carbon
    {
        $from = $from ? $from->copy()->timezone('Asia/Manila') : now('Asia/Manila');

        // Add 1 business day (24 hours)
        $expires = $from->copy()->addDay();
        while ($expires->isWeekend()) {
            $expires->addDay();
        }

        return $expires;
    }

    /**
     * Validate employee balance for requested leave type.
     */
    public function validateBalance(Employee $employee, string $leaveType, float $days): void
    {
        if ($days <= 0) {
            throw ValidationException::withMessages([
                'days_count' => 'Requested leave duration must be at least 0.5 day.',
            ]);
        }

        switch ($leaveType) {
            case 'vacation':
                $remaining = $employee->remaining_vacation_leave;
                if ($days > $remaining) {
                    throw ValidationException::withMessages([
                        'leave_type' => "Insufficient Vacation Leave balance. Requested: {$days} day(s), Remaining balance: {$remaining} day(s).",
                    ]);
                }
                break;

            case 'sick':
                $remaining = $employee->remaining_sick_leave;
                if ($days > $remaining) {
                    throw ValidationException::withMessages([
                        'leave_type' => "Insufficient Sick Leave balance. Requested: {$days} day(s), Remaining balance: {$remaining} day(s).",
                    ]);
                }
                break;

            case 'emergency':
                $remaining = $employee->remaining_emergency_leave;
                if ($days > $remaining) {
                    throw ValidationException::withMessages([
                        'leave_type' => "Insufficient Emergency Leave balance. Requested: {$days} day(s), Remaining balance: {$remaining} day(s).",
                    ]);
                }
                break;

            case 'offset':
            case 'time_off':
                $requiredHours = $days * 8.0;
                $balance = $employee->time_credit_balance;
                if ($requiredHours > $balance) {
                    throw ValidationException::withMessages([
                        'leave_type' => "Insufficient Time Off Credits. Requested: {$requiredHours} hrs ({$days} day(s)), Available balance: {$balance} hrs.",
                    ]);
                }
                break;
        }
    }

    /**
     * Check all pending leave requests and expire any whose 1-day stage limit has passed.
     */
    public function checkAndExpireOverdueRequests(): int
    {
        $overdueRequests = LeaveRequest::with(['employee.department', 'employee.user'])
            ->whereIn('status', [
                'pending_section_head',
                'pending_executive',
                'pending_executive_director',
                'pending_hr',
            ])
            ->whereNotNull('stage_expires_at')
            ->where('stage_expires_at', '<=', now())
            ->get();

        $expiredCount = 0;
        foreach ($overdueRequests as $leave) {
            $this->expireLeaveRequest($leave);
            $expiredCount++;
        }

        return $expiredCount;
    }

    /**
     * Expire a single overdue leave request and notify applicant + HR.
     */
    public function expireLeaveRequest(LeaveRequest $leave): LeaveRequest
    {
        if ($leave->status === 'expired') {
            return $leave;
        }

        $stageName = match ($leave->current_stage) {
            'section_head' => 'Section Head',
            'executive_office', 'executive' => 'Executive Office',
            'executive_director' => 'Executive Director',
            default => 'Approval',
        };

        $reason = "Application expired automatically after exceeding the 1-day (24-hour) approval limit at the {$stageName} stage.";

        $leave->update([
            'status' => 'expired',
            'expired_at' => now(),
            'rejection_reason' => $reason,
        ]);

        LeaveApproval::create([
            'leave_request_id' => $leave->id,
            'stage' => $leave->current_stage ?: 'section_head',
            'action' => 'expired',
            'approver_id' => null,
            'approver_name' => 'System Auto-Expiration',
            'approver_role' => '1-Day Approval Limit',
            'notes' => $reason,
        ]);

        // Notify Applicant
        $staffUser = $leave->employee?->user ?: ($leave->employee?->email ? User::where('email', $leave->employee->email)->first() : null);
        if ($staffUser) {
            $this->sendNotification(
                $staffUser,
                'leave_rejected',
                "Leave Application Expired (#LV-{$leave->id})",
                "Your {$leave->leave_type_label} request for {$leave->start_date->format('M d, Y')} has expired because it was not acted upon within the 1-day approval limit at the {$stageName} stage.",
                route('portal.leaves.show', $leave)
            );
        }

        // Notify HR Officers (for record & email monitoring)
        $hrUsers = $this->getHrOfficers();
        foreach ($hrUsers as $hrUser) {
            if (!$staffUser || $hrUser->id !== $staffUser->id) {
                $this->sendNotification(
                    $hrUser,
                    'leave_rejected',
                    "HR Record: Leave Request Expired (#LV-{$leave->id})",
                    "{$leave->employee?->name}'s {$leave->leave_type_label} request for {$leave->start_date->format('M d, Y')} expired after exceeding the 1-day approval limit at the {$stageName} stage.",
                    $this->resolveLeaveUrl($hrUser, $leave)
                );
            }
        }

        return $leave->fresh();
    }

    /**
     * Create and submit a new leave request.
     * Rules:
     *  - Must be filed at least 3 days before start_date.
     *  - Reason is mandatory.
     *  - Regular staff (none / hr_officer) -> Section Head (1-day limit) -> Executive Office (1-day limit).
     *  - Section Head (section_head) -> Executive Office only (1-day limit).
     *  - Executive Office (executive_office / executive_director) -> Executive Director (1-day limit).
     *  - HR is not part of the approval chain, but sees all records and receives all leave emails.
     */
    public function createRequest(Employee $employee, array $data): LeaveRequest
    {
        // Enforce filing on weekdays during working hours (8:00 AM – 5:00 PM)
        $this->validateWorkingHoursForLeave('file');

        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate = Carbon::parse($data['end_date'])->startOfDay();
        $cleanType = str_replace('_leave', '', (string) ($data['leave_type'] ?? ''));

        // Enforce 3-day advance notice ONLY for Vacation Leave.
        // Sick, Emergency, Maternity, Paternity, Solo Parent, BWL, Gynecological, and Time Off Leaves do not require 3-day advance filing.
        if ($cleanType === 'vacation') {
            $minStartDate = now('Asia/Manila')->startOfDay()->addDays(self::MIN_ADVANCE_DAYS);
            if ($startDate->lt($minStartDate)) {
                throw ValidationException::withMessages([
                    'start_date' => 'Vacation leave applications must be filed at least 3 days before the set date (earliest allowed start date: ' . $minStartDate->format('M d, Y') . ').',
                ]);
            }
        }

        // Restrict advance filing for Sick Leave.
        // Sick Leave must be filed within 3 days of return to work and must be accompanied by a medical certificate and/or Fit to Work recommendation, if SL is more than 3 days.
        if ($cleanType === 'sick') {
            $today = now('Asia/Manila')->startOfDay();
            if ($startDate->gt($today)) {
                throw ValidationException::withMessages([
                    'start_date' => 'Sick Leave must be filed within 3 days of return to work and must be accompanied by a medical certificate and/or Fit to Work recommendation, if SL is more than 3 days.',
                ]);
            }
            if ($endDate->gt($today)) {
                throw ValidationException::withMessages([
                    'end_date' => 'Sick Leave must be filed within 3 days of return to work and must be accompanied by a medical certificate and/or Fit to Work recommendation, if SL is more than 3 days.',
                ]);
            }
        }

        if ($endDate->lt($startDate)) {
            throw ValidationException::withMessages([
                'end_date' => 'End date cannot be earlier than start date.',
            ]);
        }

        $reason = trim((string) ($data['reason'] ?? ''));
        if ($reason === '') {
            throw ValidationException::withMessages([
                'reason' => 'Reason for leave is required.',
            ]);
        }

        $days = (float) ($data['days_count'] ?? 1.0);
        if (!empty($data['is_half_day'])) {
            $days = 0.5;
        }

        $this->validateBalance($employee, $data['leave_type'], $days);

        // Determine initial routing stage based on applicant's role:
        // - Section Head -> Executive Office only (1-day limit)
        // - Executive Office / Executive Director -> Executive Director (1-day limit)
        // - Regular Staff / HR Officer -> Section Head (1-day limit), then Executive Office (1-day limit)
        if ($employee->approval_role === 'section_head') {
            $initialStatus = 'pending_executive';
            $initialStage = 'executive_office';
            $stageDescription = 'Pending Executive Office Approval (1-Day Limit)';
        } elseif (in_array($employee->approval_role, ['executive_office', 'executive_director'])) {
            $initialStatus = 'pending_executive_director';
            $initialStage = 'executive_director';
            $stageDescription = 'Pending Executive Director Approval (1-Day Limit)';
        } else {
            $initialStatus = 'pending_section_head';
            $initialStage = 'section_head';
            $stageDescription = 'Pending Section Head Approval (1-Day Limit)';
        }

        $stageExpiresAt = $this->calculateStageExpiration();

        $request = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => $data['leave_type'],
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'days_count' => $days,
            'is_half_day' => !empty($data['is_half_day']),
            'half_day_type' => $data['half_day_type'] ?? null,
            'reason' => $reason,
            'attachment' => $data['attachment'] ?? null,
            'status' => $initialStatus,
            'current_stage' => $initialStage,
            'stage_expires_at' => $stageExpiresAt,
        ]);

        $notifiedIds = [];

        // 1. Notify the applying employee
        $applicantUser = $employee->user ?: ($employee->email ? User::where('email', $employee->email)->first() : null);
        if ($applicantUser) {
            $notifiedIds[] = $applicantUser->id;
            $this->sendNotification(
                $applicantUser,
                'leave_submitted',
                "Leave Request Submitted (#LV-{$request->id})",
                "Your request for {$request->leave_type_label} ({$request->days_count} day(s)) from {$startDate->format('M d, Y')} to {$endDate->format('M d, Y')} was submitted. Current Status: {$stageDescription} (Expires: {$stageExpiresAt->timezone('Asia/Manila')->format('M d, Y h:i A')}).",
                route('portal.leaves.show', $request)
            );
        }

        // 2. Notify the designated Approver(s) for the initial stage
        if ($initialStage === 'section_head') {
            $sectionHeads = $this->getSectionHeads($employee);
            foreach ($sectionHeads as $head) {
                if (!in_array($head->id, $notifiedIds)) {
                    $notifiedIds[] = $head->id;
                    $this->sendNotification(
                        $head,
                        'leave_submitted',
                        "Action Required (1-Day Limit): Approve Leave (#LV-{$request->id})",
                        "{$employee->name} ({$employee->department?->name}) filed a {$request->leave_type_label} for {$startDate->format('M d, Y')}. Please approve or disapprove within 24 hours (before {$stageExpiresAt->timezone('Asia/Manila')->format('M d, Y h:i A')}).",
                        $this->resolveLeaveUrl($head, $request)
                    );
                }
            }
        } elseif ($initialStage === 'executive_office') {
            $execUsers = $this->getExecutiveApprovers();
            foreach ($execUsers as $execUser) {
                if (!in_array($execUser->id, $notifiedIds)) {
                    $notifiedIds[] = $execUser->id;
                    $this->sendNotification(
                        $execUser,
                        'leave_submitted',
                        "Action Required (1-Day Limit): Section Head Leave (#LV-{$request->id})",
                        "Section Head {$employee->name} ({$employee->department?->name}) filed a {$request->leave_type_label} for {$startDate->format('M d, Y')}. Awaiting Executive Office approval within 24 hours.",
                        $this->resolveLeaveUrl($execUser, $request)
                    );
                }
            }
        } elseif ($initialStage === 'executive_director') {
            $directorUsers = $this->getExecutiveDirectors();
            foreach ($directorUsers as $dirUser) {
                if (!in_array($dirUser->id, $notifiedIds)) {
                    $notifiedIds[] = $dirUser->id;
                    $this->sendNotification(
                        $dirUser,
                        'leave_submitted',
                        "Action Required (1-Day Limit): Executive Office Leave (#LV-{$request->id})",
                        "{$employee->name} (Executive Office) filed a {$request->leave_type_label} for {$startDate->format('M d, Y')}. Awaiting Executive Director approval within 24 hours.",
                        $this->resolveLeaveUrl($dirUser, $request)
                    );
                }
            }
        }

        // 3. Always notify HR Officers & Admins for monitoring/records (even though HR is not in the approval chain)
        $hrUsers = $this->getHrOfficers();
        foreach ($hrUsers as $hrUser) {
            if (!in_array($hrUser->id, $notifiedIds)) {
                $notifiedIds[] = $hrUser->id;
                $this->sendNotification(
                    $hrUser,
                    'leave_submitted',
                    "HR Record: New Leave Application Filed (#LV-{$request->id})",
                    "{$employee->name} ({$employee->department?->name}) filed a {$request->leave_type_label} ({$request->days_count} day(s)) for {$startDate->format('M d, Y')} to {$endDate->format('M d, Y')}. Status: {$stageDescription}. (HR Information Copy)",
                    $this->resolveLeaveUrl($hrUser, $request)
                );
            }
        }

        return $request;
    }

    /**
     * Process multi-tier review (Approve / Reject).
     * Note: HR is no longer an approval stage.
     */
    public function processApproval(LeaveRequest $leave, User $approver, string $stage, string $action, ?string $notes = null): LeaveRequest
    {
        // Enforce approval on weekdays during working hours (8:00 AM – 5:00 PM)
        $this->validateWorkingHoursForLeave('approve');

        // Check if request has already expired before processing
        if ($leave->status === 'expired' || ($leave->is_pending && $leave->stage_expires_at && $leave->stage_expires_at->isPast())) {
            $this->expireLeaveRequest($leave);
            throw ValidationException::withMessages([
                'stage' => 'This leave request has expired because the 1-day approval time limit was exceeded.',
            ]);
        }

        if ($stage === 'hr') {
            throw ValidationException::withMessages([
                'stage' => 'HR is no longer part of the leave approval process. HR receives leave records and email notifications for monitoring only.',
            ]);
        }

        return DB::transaction(function () use ($leave, $approver, $stage, $action, $notes) {
            $cleanNotes = !empty(trim((string) $notes)) ? trim((string) $notes) : null;

            $approverEmp = $approver->employee;
            $isSuperAdmin = (!$approverEmp && $approver->email === 'admin@lasac.com');

            if (!$isSuperAdmin) {
                if (!$approverEmp) {
                    throw ValidationException::withMessages([
                        'stage' => 'Your account does not have an employee profile linked to an authorized approval role.',
                    ]);
                }

                if ($approverEmp->id === $leave->employee_id) {
                    throw ValidationException::withMessages([
                        'stage' => 'You cannot approve or review your own leave request.',
                    ]);
                }

                if ($stage === 'section_head') {
                    if ($approverEmp->approval_role !== 'section_head') {
                        throw ValidationException::withMessages([
                            'stage' => 'Only designated Section Heads can approve Stage 1 leave requests.',
                        ]);
                    }
                    if ($approverEmp->department_id !== $leave->employee->department_id) {
                        throw ValidationException::withMessages([
                            'stage' => 'Section Heads can only approve leave requests for staff in their own department.',
                        ]);
                    }
                    if (!in_array($leave->employee->approval_role, ['none', 'hr_officer'])) {
                        throw ValidationException::withMessages([
                            'stage' => 'Section Heads can only approve leave requests for regular department staff.',
                        ]);
                    }
                } elseif ($stage === 'executive_office') {
                    if (!in_array($approverEmp->approval_role, ['executive_office', 'executive_director'])) {
                        throw ValidationException::withMessages([
                            'stage' => 'Only the Executive Office can grant approval at this stage.',
                        ]);
                    }
                } elseif ($stage === 'executive_director') {
                    if ($approverEmp->approval_role !== 'executive_director') {
                        throw ValidationException::withMessages([
                            'stage' => 'Only the Executive Director can approve leave requests filed by the Executive Office.',
                        ]);
                    }
                } else {
                    throw ValidationException::withMessages([
                        'stage' => 'Invalid leave approval stage.',
                    ]);
                }
            }

            // Determine approver role title
            $approverRole = match ($stage) {
                'section_head' => 'Section Head',
                'executive_office' => 'Executive Office',
                'executive_director' => 'Executive Director',
                default => 'Approving Officer',
            };

            if ($isSuperAdmin) {
                $approverRole .= ' (System Admin)';
            }

            // Handle Rejection
            if ($action === 'rejected') {
                $leave->update([
                    'status' => 'rejected',
                    'stage_expires_at' => null,
                    'rejection_reason' => $cleanNotes ?? 'Disapproved during review',
                    'rejected_by' => $approver->id,
                    'rejected_at' => now(),
                ]);

                LeaveApproval::create([
                    'leave_request_id' => $leave->id,
                    'stage' => $stage,
                    'action' => 'rejected',
                    'approver_id' => $approver->id,
                    'approver_name' => $approver->name,
                    'approver_role' => $approverRole,
                    'notes' => $cleanNotes,
                ]);

                $stageName = match ($stage) {
                    'section_head' => 'Section Head',
                    'executive_office' => 'Executive Office',
                    'executive_director' => 'Executive Director',
                    default => 'Review Body',
                };
                $noteText = $cleanNotes ? " Reason/Notes: \"{$cleanNotes}\"" : "";

                // Notify Staff of Disapproval
                $staffUser = $leave->employee->user ?: ($leave->employee->email ? User::where('email', $leave->employee->email)->first() : null);
                $notifiedIds = [$approver->id];
                if ($staffUser) {
                    $notifiedIds[] = $staffUser->id;
                    $this->sendNotification(
                        $staffUser,
                        'leave_rejected',
                        "Leave Application Disapproved at {$stageName}",
                        "Your {$leave->leave_type_label} application for {$leave->start_date->format('M d, Y')} was disapproved at the {$stageName} stage by {$approver->name}.{$noteText}",
                        route('portal.leaves.show', $leave)
                    );
                }

                // Notify HR Officers for record monitoring
                foreach ($this->getHrOfficers() as $hrUser) {
                    if (!in_array($hrUser->id, $notifiedIds)) {
                        $notifiedIds[] = $hrUser->id;
                        $this->sendNotification(
                            $hrUser,
                            'leave_rejected',
                            "HR Record: Leave Disapproved (#LV-{$leave->id})",
                            "{$leave->employee->name}'s {$leave->leave_type_label} application for {$leave->start_date->format('M d, Y')} was disapproved by {$approver->name} ({$stageName}).{$noteText}",
                            $this->resolveLeaveUrl($hrUser, $leave)
                        );
                    }
                }

                return $leave->fresh();
            }

            // Handle Approvals / Progressions
            if ($stage === 'section_head') {
                // Stage 1 (Section Head) -> Advance directly to Executive Office with a fresh 1-business-day limit (skipping weekends)
                $nextExpiresAt = $this->calculateStageExpiration();

                $leave->update([
                    'status' => 'pending_executive',
                    'current_stage' => 'executive_office',
                    'stage_expires_at' => $nextExpiresAt,
                ]);

                LeaveApproval::create([
                    'leave_request_id' => $leave->id,
                    'stage' => 'section_head',
                    'action' => 'approved',
                    'approver_id' => $approver->id,
                    'approver_name' => $approver->name,
                    'approver_role' => $approverRole,
                    'notes' => $cleanNotes,
                ]);

                $notifiedIds = [$approver->id];

                // Notify Staff of Stage 1 Progress
                $staffUser = $leave->employee->user ?: ($leave->employee->email ? User::where('email', $leave->employee->email)->first() : null);
                if ($staffUser) {
                    $notifiedIds[] = $staffUser->id;
                    $noteText = $cleanNotes ? " Notes: \"{$cleanNotes}\"." : "";
                    $this->sendNotification(
                        $staffUser,
                        'leave_section_approved',
                        "Leave Progress: Section Head Approved (Stage 1 of 2)",
                        "Good news! Your Section Head ({$approver->name}) has approved your {$leave->leave_type_label}.{$noteText} Your application is now with the Executive Office for final approval (1-day approval window).",
                        route('portal.leaves.show', $leave)
                    );
                }

                // Notify Executive Office Approvers (Action Required within 1 day)
                $execUsers = $this->getExecutiveApprovers();
                foreach ($execUsers as $execUser) {
                    if (!in_array($execUser->id, $notifiedIds)) {
                        $notifiedIds[] = $execUser->id;
                        $this->sendNotification(
                            $execUser,
                            'leave_section_approved',
                            "Action Required (1-Day Limit): Executive Approval (#LV-{$leave->id})",
                            "{$leave->employee->name}'s {$leave->leave_type_label} was approved by Section Head ({$approver->name}). Awaiting Executive Office final approval within 24 hours.",
                            $this->resolveLeaveUrl($execUser, $leave)
                        );
                    }
                }

                // Notify HR Officers (Information Copy / Record Monitoring)
                $hrUsers = $this->getHrOfficers();
                foreach ($hrUsers as $hrUser) {
                    if (!in_array($hrUser->id, $notifiedIds)) {
                        $notifiedIds[] = $hrUser->id;
                        $this->sendNotification(
                            $hrUser,
                            'leave_section_approved',
                            "HR Record: Section Head Approved Leave (#LV-{$leave->id})",
                            "{$leave->employee->name}'s {$leave->leave_type_label} was approved by Section Head ({$approver->name}) and is now pending Executive Office approval.",
                            $this->resolveLeaveUrl($hrUser, $leave)
                        );
                    }
                }
            } elseif (in_array($stage, ['executive_office', 'executive_director'])) {
                // Final Approval (by Executive Office or Executive Director)
                $leave->update([
                    'status' => 'approved',
                    'current_stage' => 'completed',
                    'stage_expires_at' => null,
                    'approved_at' => now(),
                ]);

                LeaveApproval::create([
                    'leave_request_id' => $leave->id,
                    'stage' => $stage,
                    'action' => 'approved',
                    'approver_id' => $approver->id,
                    'approver_name' => $approver->name,
                    'approver_role' => $approverRole,
                    'notes' => $cleanNotes,
                ]);

                // Final Action 1: Create or update Attendance Records to 'leave'
                $period = CarbonPeriod::create($leave->start_date, $leave->end_date);
                foreach ($period as $date) {
                    AttendanceRecord::updateOrCreate(
                        [
                            'employee_id' => $leave->employee_id,
                            'date' => $date->format('Y-m-d'),
                        ],
                        [
                            'status' => 'leave',
                            'source' => 'leave_request',
                            'hours_worked' => 0,
                            'late_minutes' => 0,
                            'undertime_minutes' => 0,
                        ]
                    );
                }

                // Final Action 2: If Time Off / Offset Leave, deduct hours from TimeCreditLedger
                if (in_array($leave->leave_type, ['offset', 'time_off'])) {
                    $hoursToDeduct = (float) ($leave->days_count * 8.0);
                    $newBalance = (float) $leave->employee->time_credit_balance - $hoursToDeduct;

                    TimeCreditLedger::create([
                        'employee_id' => $leave->employee_id,
                        'date' => $leave->start_date,
                        'type' => 'deduction',
                        'hours' => -$hoursToDeduct,
                        'reason' => "Time Off Leave: {$leave->start_date->format('M d, Y')} to {$leave->end_date->format('M d, Y')} (Req #{$leave->id})",
                        'running_balance' => $newBalance,
                        'created_by' => $approver->id,
                    ]);
                }

                // Notify Staff of Final Approval
                $staffUser = $leave->employee->user ?: ($leave->employee->email ? User::where('email', $leave->employee->email)->first() : null);
                if ($staffUser) {
                    $noteText = $cleanNotes ? " Final Notes: \"{$cleanNotes}\"" : "";
                    $this->sendNotification(
                        $staffUser,
                        'leave_approved',
                        "🎉 Leave Application Approved (#LV-{$leave->id})",
                        "Congratulations! Your {$leave->leave_type_label} application for {$leave->start_date->format('M d, Y')} to {$leave->end_date->format('M d, Y')} ({$leave->days_count} day(s)) has been officially approved by {$approverRole} ({$approver->name})!{$noteText}",
                        route('portal.leaves.show', $leave)
                    );
                }

                // Notify Section Head & HR Officers of Final Approval for records
                $notifiedUserIds = array_filter([$approver->id, $leave->employee->user_id]);
                $reviewers = $this->getSectionHeads($leave->employee)->merge($this->getHrOfficers());
                foreach ($reviewers as $reviewer) {
                    if (!in_array($reviewer->id, $notifiedUserIds)) {
                        $notifiedUserIds[] = $reviewer->id;
                        $this->sendNotification(
                            $reviewer,
                            'leave_approved',
                            "HR Record: Leave Approved (#LV-{$leave->id})",
                            "{$leave->employee->name}'s {$leave->leave_type_label} ({$leave->days_count} day(s)) from {$leave->start_date->format('M d, Y')} to {$leave->end_date->format('M d, Y')} has received final approval from {$approverRole} ({$approver->name}).",
                            $this->resolveLeaveUrl($reviewer, $leave)
                        );
                    }
                }
            }

            return $leave->fresh();
        });
    }

    /**
     * Send in-app + email notification.
     */
    public function sendNotification(User $user, string $type, string $title, string $message, ?string $link = null): AppNotification
    {
        return app(NotificationService::class)->notifyUser($user, $type, $title, $message, $link);
    }

    /**
     * Get all HR Officers & System Super-Admins (for informational notifications and record visibility).
     */
    public function getHrOfficers(): \Illuminate\Database\Eloquent\Collection
    {
        $byRelation = User::whereHas('employee', fn($q) => $q->where('approval_role', 'hr_officer'))->get();
        $bySuperAdmin = User::role('admin')->whereDoesntHave('employee')->get();
        $byEmail = User::whereIn('email', Employee::where('approval_role', 'hr_officer')->whereNotNull('email')->pluck('email'))->get();

        return $byRelation->merge($bySuperAdmin)->merge($byEmail)->unique('id');
    }

    /**
     * Get all Executive Office approvers & System Super-Admins.
     */
    public function getExecutiveApprovers(): \Illuminate\Database\Eloquent\Collection
    {
        $byRelation = User::whereHas('employee', fn($q) => $q->whereIn('approval_role', ['executive_office', 'executive_director']))->get();
        $bySuperAdmin = User::role('admin')->whereDoesntHave('employee')->get();
        $byEmail = User::whereIn('email', Employee::whereIn('approval_role', ['executive_office', 'executive_director'])->whereNotNull('email')->pluck('email'))->get();

        return $byRelation->merge($bySuperAdmin)->merge($byEmail)->unique('id');
    }

    /**
     * Get all Executive Director approvers & System Super-Admins.
     */
    public function getExecutiveDirectors(): \Illuminate\Database\Eloquent\Collection
    {
        $byRelation = User::whereHas('employee', fn($q) => $q->where('approval_role', 'executive_director'))->get();
        $bySuperAdmin = User::role('admin')->whereDoesntHave('employee')->get();
        $byEmail = User::whereIn('email', Employee::where('approval_role', 'executive_director')->whereNotNull('email')->pluck('email'))->get();

        return $byRelation->merge($bySuperAdmin)->merge($byEmail)->unique('id');
    }

    /**
     * Get Section Heads for an employee's department.
     */
    public function getSectionHeads(Employee $employee): \Illuminate\Database\Eloquent\Collection
    {
        $byRelation = User::whereHas('employee', fn($q) => $q->where('department_id', $employee->department_id)->where('approval_role', 'section_head'))->get();
        $byEmail = User::whereIn('email', Employee::where('department_id', $employee->department_id)->where('approval_role', 'section_head')->whereNotNull('email')->pluck('email'))->get();

        return $byRelation->merge($byEmail)->unique('id');
    }

    /**
     * Manual Leave Encoding by HR:
     * Directly encodes and registers an authorized leave for an employee.
     * Bypasses standard 3-day advance notice and office-hours restrictions.
     * Automatically registers attendance as 'leave', deducts balances, creates approval record, and notifies the employee.
     */
    public function createManualRequestByHr(Employee $employee, array $data, User $hrUser): LeaveRequest
    {
        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate = Carbon::parse($data['end_date'])->startOfDay();
        $cleanType = str_replace('_leave', '', (string) ($data['leave_type'] ?? ''));

        if ($cleanType === 'sick') {
            $today = now('Asia/Manila')->startOfDay();
            if ($startDate->gt($today)) {
                throw ValidationException::withMessages([
                    'start_date' => 'Sick Leave must be filed within 3 days of return to work and must be accompanied by a medical certificate and/or Fit to Work recommendation, if SL is more than 3 days.',
                ]);
            }
            if ($endDate->gt($today)) {
                throw ValidationException::withMessages([
                    'end_date' => 'Sick Leave must be filed within 3 days of return to work and must be accompanied by a medical certificate and/or Fit to Work recommendation, if SL is more than 3 days.',
                ]);
            }
        }

        if ($endDate->lt($startDate)) {
            throw ValidationException::withMessages([
                'end_date' => 'End date cannot be earlier than start date.',
            ]);
        }

        $reason = trim((string) ($data['reason'] ?? ''));
        if ($reason === '') {
            throw ValidationException::withMessages([
                'reason' => 'Reason for leave is required.',
            ]);
        }

        $days = (float) ($data['days_count'] ?? 1.0);
        if (!empty($data['is_half_day'])) {
            $days = 0.5;
        }

        // Validate balance
        $this->validateBalance($employee, $data['leave_type'], $days);

        return DB::transaction(function () use ($employee, $data, $startDate, $endDate, $days, $reason, $hrUser) {
            $leave = LeaveRequest::create([
                'employee_id' => $employee->id,
                'leave_type' => $data['leave_type'],
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'days_count' => $days,
                'is_half_day' => !empty($data['is_half_day']),
                'half_day_type' => $data['half_day_type'] ?? null,
                'reason' => $reason,
                'attachment' => $data['attachment'] ?? null,
                'status' => 'approved',
                'current_stage' => 'completed',
                'stage_expires_at' => null,
            ]);

            // Create Approval Audit Record
            LeaveApproval::create([
                'leave_request_id' => $leave->id,
                'stage' => 'hr_manual',
                'action' => 'approved',
                'approver_id' => $hrUser->id,
                'approver_name' => $hrUser->name,
                'approver_role' => 'HR Manual Encoder',
                'notes' => $data['notes'] ?? 'Directly encoded and approved by HR Administration.',
            ]);

            // Register Attendance Records as 'leave'
            $period = CarbonPeriod::create($leave->start_date, $leave->end_date);
            foreach ($period as $date) {
                AttendanceRecord::updateOrCreate(
                    [
                        'employee_id' => $leave->employee_id,
                        'date' => $date->format('Y-m-d'),
                    ],
                    [
                        'status' => 'leave',
                        'source' => 'hr_manual_encoding',
                        'hours_worked' => 0,
                        'late_minutes' => 0,
                        'undertime_minutes' => 0,
                    ]
                );
            }

            // Deduct Time Credits if Time Off / Offset Leave
            if (in_array($leave->leave_type, ['offset', 'time_off'])) {
                $hoursToDeduct = (float) ($leave->days_count * 8.0);
                $newBalance = (float) $leave->employee->time_credit_balance - $hoursToDeduct;

                TimeCreditLedger::create([
                    'employee_id' => $leave->employee_id,
                    'date' => $leave->start_date,
                    'type' => 'deduction',
                    'hours' => -$hoursToDeduct,
                    'reason' => "Time Off Leave (HR Encoded): {$leave->start_date->format('M d, Y')} to {$leave->end_date->format('M d, Y')} (Req #{$leave->id})",
                    'running_balance' => $newBalance,
                    'created_by' => $hrUser->id,
                ]);
            }

            // Notify Staff of HR-Encoded Leave
            $staffUser = $employee->user ?: ($employee->email ? User::where('email', $employee->email)->first() : null);
            if ($staffUser) {
                $this->sendNotification(
                    $staffUser,
                    'leave_approved',
                    "📋 Leave Record Encoded (#LV-{$leave->id})",
                    "HR Administration ({$hrUser->name}) has recorded an approved {$leave->leave_type_label} for you covering {$leave->start_date->format('M d, Y')} to {$leave->end_date->format('M d, Y')} ({$leave->days_count} day(s)). Reason: {$leave->reason}.",
                    route('portal.leaves.show', $leave)
                );
            }

            return $leave;
        });
    }

    /**
     * Resolve target URL for leave based on user role.
     */
    public function resolveLeaveUrl(User $user, LeaveRequest $leave): string
    {
        return $user->hasRole('admin')
            ? route('admin.leaves.show', $leave)
            : route('portal.leaves.show', $leave);
    }
}
