<?php
namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\TimeCreditLedger;
use Carbon\Carbon;

class TimeCreditService
{
    /**
     * Process time credit for a single attendance record.
     * Rules:
     *  - Weekday after 5:00 PM → excess minutes after 17:00 (e.g. 5:30 PM = 30 mins / 0.50 hrs)
     *  - Weekend (Sat/Sun) → all minutes worked
     *  - Any shift crossing midnight → cap total credit at 4 hours (240 mins)
     *  - Credit is initially recorded as PENDING and is NOT VALID until HR/Admin approves it.
     */
    public function processRecord(AttendanceRecord $record): ?TimeCreditLedger
    {
        if (!$record->time_in || !$record->time_out) {
            return null;
        }

        $date = Carbon::parse($record->date);
        $timeIn = Carbon::parse($record->date->format('Y-m-d') . ' ' . $record->time_in);
        $timeOut = Carbon::parse($record->date->format('Y-m-d') . ' ' . $record->time_out);

        // Handle overnight (time_out <= time_in means next day)
        $isOvernight = false;
        if ($timeOut->lessThanOrEqualTo($timeIn)) {
            $timeOut->addDay();
            $isOvernight = true;
        }

        $overtimeMinutes = 0;
        $reason = '';
        $isWeekend = $date->isWeekend();

        if ($isWeekend) {
            // All minutes worked on weekend
            $overtimeMinutes = (int) $timeIn->diffInMinutes($timeOut);
            $reason = 'Weekend work (' . $date->format('D, M d') . ', ' . $overtimeMinutes . ' mins)';
        } else {
            // Weekday: only excess minutes after 5:00 PM
            $overtimeStart = Carbon::parse($record->date->format('Y-m-d') . ' 17:00:00');

            if ($timeOut->greaterThan($overtimeStart)) {
                $actualStart = $timeIn->greaterThan($overtimeStart) ? $timeIn : $overtimeStart;
                $overtimeMinutes = (int) $actualStart->diffInMinutes($timeOut);
                $reason = "{$overtimeMinutes} mins excess time (Out at " . $timeOut->format('h:i A') . ")";
            }
        }

        // Cap at 4 hours (240 minutes) for overnight shifts
        if ($isOvernight && $overtimeMinutes > 240) {
            $overtimeMinutes = 240;
            $reason .= ' [capped - overnight]';
        }

        if ($overtimeMinutes <= 0) {
            if ($record->overtime_minutes > 0 || $record->overtime_status !== 'none') {
                $record->update([
                    'overtime_minutes' => 0,
                    'overtime_status'  => 'none',
                ]);
            }
            return null;
        }

        $creditHours = round($overtimeMinutes / 60, 2);

        // Determine overtime status for attendance record
        $currentRecordStatus = $record->overtime_status;
        $effectiveStatus = in_array($currentRecordStatus, ['approved', 'rejected']) 
            ? $currentRecordStatus 
            : 'pending';

        $record->update([
            'overtime_minutes' => $overtimeMinutes,
            'overtime_status'  => $effectiveStatus,
        ]);

        // Check if ledger entry already exists
        $existingEntry = $record->timeCreditEntry;
        if ($existingEntry) {
            $existingEntry->update([
                'hours'  => $creditHours,
                'reason' => $reason,
            ]);
            return $existingEntry;
        }

        // Current approved balance remains unchanged until HR/Admin approves
        $currentBalance = $this->getBalance($record->employee);

        return TimeCreditLedger::create([
            'employee_id'          => $record->employee_id,
            'date'                 => $record->date,
            'type'                 => 'accrual',
            'status'               => $effectiveStatus,
            'hours'                => $creditHours,
            'reason'               => $reason,
            'running_balance'      => $currentBalance,
            'attendance_record_id' => $record->id,
            'created_by'           => auth()->id(),
        ]);
    }

    /**
     * Manual adjustment (admin adds or deducts credits directly).
     */
    public function adjust(Employee $employee, float $hours, string $reason, ?string $date = null): TimeCreditLedger
    {
        $currentBalance = $this->getBalance($employee);
        $newBalance = round($currentBalance + $hours, 2);

        $entry = TimeCreditLedger::create([
            'employee_id'     => $employee->id,
            'date'            => $date ?? now()->toDateString(),
            'type'            => $hours < 0 ? 'deduction' : 'adjustment',
            'status'          => 'approved',
            'hours'           => $hours,
            'reason'          => $reason,
            'running_balance' => $newBalance,
            'created_by'      => auth()->id(),
            'approved_by'     => auth()->id(),
            'approved_at'     => now(),
        ]);

        app(NotificationService::class)->notifyTimeCreditAdjustment($employee, $hours, $reason, $newBalance);

        return $entry;
    }

    /**
     * Approve a pending time credit ledger entry.
     */
    public function approve(TimeCreditLedger $entry, ?int $adminId = null): TimeCreditLedger
    {
        if ($entry->status !== 'pending') {
            return $entry;
        }

        $adminId = $adminId ?? auth()->id();
        $employee = $entry->employee;
        $currentBalance = $this->getBalance($employee);
        $newBalance = round($currentBalance + (float)$entry->hours, 2);

        $entry->update([
            'status'          => 'approved',
            'approved_by'     => $adminId,
            'approved_at'     => now(),
            'running_balance' => $newBalance,
        ]);

        if ($entry->attendance_record_id && $entry->attendanceRecord) {
            $entry->attendanceRecord->update([
                'overtime_status'      => 'approved',
                'overtime_approved_by' => $adminId,
                'overtime_approved_at' => now(),
            ]);
        }

        app(NotificationService::class)->notifyTimeCreditApproved($entry);

        return $entry;
    }

    /**
     * Reject a pending time credit ledger entry.
     */
    public function reject(TimeCreditLedger $entry, ?string $reason = null, ?int $adminId = null): TimeCreditLedger
    {
        if ($entry->status !== 'pending') {
            return $entry;
        }

        $adminId = $adminId ?? auth()->id();

        $entry->update([
            'status'           => 'rejected',
            'approved_by'      => $adminId,
            'approved_at'      => now(),
            'rejection_reason' => $reason,
        ]);

        if ($entry->attendance_record_id && $entry->attendanceRecord) {
            $entry->attendanceRecord->update([
                'overtime_status'      => 'rejected',
                'overtime_approved_by' => $adminId,
                'overtime_approved_at' => now(),
            ]);
        }

        app(NotificationService::class)->notifyTimeCreditRejected($entry, $reason);

        return $entry;
    }

    /**
     * Approve overtime directly from an AttendanceRecord.
     */
    public function approveAttendanceOvertime(AttendanceRecord $record, ?int $adminId = null): AttendanceRecord
    {
        $adminId = $adminId ?? auth()->id();
        $entry = $record->timeCreditEntry ?: $this->processRecord($record);

        if ($entry) {
            $this->approve($entry, $adminId);
        } else {
            $record->update([
                'overtime_status'      => 'approved',
                'overtime_approved_by' => $adminId,
                'overtime_approved_at' => now(),
            ]);
        }

        return $record->fresh();
    }

    /**
     * Reject overtime directly from an AttendanceRecord.
     */
    public function rejectAttendanceOvertime(AttendanceRecord $record, ?string $reason = null, ?int $adminId = null): AttendanceRecord
    {
        $adminId = $adminId ?? auth()->id();
        $entry = $record->timeCreditEntry ?: $this->processRecord($record);

        if ($entry) {
            $this->reject($entry, $reason, $adminId);
        } else {
            $record->update([
                'overtime_status'      => 'rejected',
                'overtime_approved_by' => $adminId,
                'overtime_approved_at' => now(),
            ]);
        }

        return $record->fresh();
    }

    /**
     * Approve all pending time credit entries.
     */
    public function approveAllPending(?int $adminId = null): int
    {
        $pending = TimeCreditLedger::where('status', 'pending')->get();
        $count = 0;
        foreach ($pending as $entry) {
            $this->approve($entry, $adminId);
            $count++;
        }
        return $count;
    }

    /**
     * Deduct hours when offset leave is consumed.
     */
    public function deduct(Employee $employee, float $hours, string $reason): TimeCreditLedger
    {
        return $this->adjust($employee, -abs($hours), $reason);
    }

    /**
     * Get approved available balance.
     */
    public function getBalance(Employee $employee): float
    {
        return (float) TimeCreditLedger::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->sum('hours');
    }

    /**
     * Get total pending unverified credit hours.
     */
    public function getPendingHours(Employee $employee): float
    {
        return (float) TimeCreditLedger::where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->sum('hours');
    }
}
