<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RfidAttendanceSyncService
{
    /**
     * Check if the RFID attendance database connection is active and reachable.
     */
    public function isConnected(): bool
    {
        try {
            DB::connection('rfid')->getPdo();
            return DB::connection('rfid')->getSchemaBuilder()->hasTable('attendance_logs');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Synchronize RFID attendance logs for a specific employee within a date range.
     * Default date range is today and the previous 3 days.
     */
    public function syncForEmployee(Employee $employee, ?string $startDate = null, ?string $endDate = null): int
    {
        $startDate = $startDate ?: now()->subDays(3)->toDateString();
        $endDate   = $endDate   ?: now()->toDateString();

        return $this->syncDateRange($startDate, $endDate, $employee->id);
    }

    /**
     * Synchronize all RFID attendance logs across all history.
     */
    public function syncAll(?int $hrisEmployeeId = null): int
    {
        if (!$this->isConnected()) {
            return 0;
        }

        $minDate = DB::connection('rfid')->table('attendance_logs')->min('log_date');
        $maxDate = DB::connection('rfid')->table('attendance_logs')->max('log_date');

        if (!$minDate || !$maxDate) {
            return 0;
        }

        return $this->syncDateRange((string) $minDate, (string) $maxDate, $hrisEmployeeId);
    }

    /**
     * Synchronize RFID attendance logs across all or a specific employee for a date range.
     */
    public function syncDateRange(string $startDate, string $endDate, ?int $hrisEmployeeId = null): int
    {
        if (!$this->isConnected()) {
            Log::info('RFID attendance database connection not available. Skipping sync.');
            return 0;
        }

        // Build mapping: [ rfid_emp_id => hris_emp_id ]
        $rfidToHrisMap = $this->buildEmployeeMap();

        if (empty($rfidToHrisMap)) {
            return 0;
        }

        // If filtering by a single HRIS employee, find their corresponding RFID employee ID(s)
        $filterRfidIds = null;
        if ($hrisEmployeeId !== null) {
            $filterRfidIds = array_keys(array_filter($rfidToHrisMap, fn($hId) => $hId === $hrisEmployeeId));
            if (empty($filterRfidIds)) {
                return 0;
            }
        }

        // Fetch attendance logs from the rfid database
        $query = DB::connection('rfid')->table('attendance_logs')
            ->whereBetween('log_date', [$startDate, $endDate]);

        if ($filterRfidIds !== null) {
            $query->whereIn('employee_id', $filterRfidIds);
        }

        $logs = $query->orderBy('log_date', 'asc')
            ->orderBy('log_time', 'asc')
            ->get();

        if ($logs->isEmpty()) {
            return 0;
        }

        // Group by [ rfid_employee_id ][ log_date ]
        $grouped = [];
        foreach ($logs as $log) {
            $grouped[$log->employee_id][$log->log_date][] = $log;
        }

        $syncedCount = 0;

        foreach ($grouped as $rfidEmpId => $dates) {
            $targetHrisId = $rfidToHrisMap[$rfidEmpId] ?? null;
            if (!$targetHrisId) {
                continue;
            }

            foreach ($dates as $dateStr => $dayLogs) {
                if ($this->processDayLogs($targetHrisId, $dateStr, $dayLogs)) {
                    $syncedCount++;
                }
            }
        }

        return $syncedCount;
    }

    /**
     * Process logs for one employee on one date into an attendance_records row.
     */
    protected function processDayLogs(int $hrisEmployeeId, string $dateStr, array $dayLogs): bool
    {
        $timeInLog = null;
        $timeOutLog = null;

        // Separate TIME_IN and TIME_OUT
        $inLogs = array_filter($dayLogs, fn($l) => strtoupper($l->type ?? '') === 'TIME_IN');
        $outLogs = array_filter($dayLogs, fn($l) => strtoupper($l->type ?? '') === 'TIME_OUT');

        if (!empty($inLogs)) {
            $timeInLog = reset($inLogs); // earliest TIME_IN
        }

        if (!empty($outLogs)) {
            $timeOutLog = end($outLogs); // latest TIME_OUT
        }

        // Fallback: if logs don't use enum strictly or only 1 punch type was used
        if (!$timeInLog && !empty($dayLogs)) {
            $timeInLog = reset($dayLogs);
        }
        if (!$timeOutLog && count($dayLogs) > 1) {
            $last = end($dayLogs);
            if ($last !== $timeInLog) {
                $timeOutLog = $last;
            }
        }

        $timeIn = $timeInLog ? Carbon::parse($timeInLog->log_time)->format('H:i:s') : null;
        $timeOut = $timeOutLog ? Carbon::parse($timeOutLog->log_time)->format('H:i:s') : null;

        // Enforce Time-In restriction if employee has an overdue Notice to Explain (NTE)
        $employee = Employee::find($hrisEmployeeId);
        if ($employee && $employee->hasOverdueNte($dateStr)) {
            $timeIn = null;
            Log::warning("Time-in blocked for employee #{$hrisEmployeeId} on {$dateStr} due to overdue Notice to Explain (NTE).");
        }

        if (!$timeIn && !$timeOut) {
            return false;
        }

        $date = Carbon::parse($dateStr);
        $isWeekend = $date->isWeekend();

        $hoursWorked = 0;
        $lateMinutes = 0;
        $undertimeMinutes = 0;
        $overtimeMinutes = 0;
        $status = 'present';

        if ($timeIn && $timeOut) {
            $inDT = Carbon::parse("{$dateStr} {$timeIn}");
            $outDT = Carbon::parse("{$dateStr} {$timeOut}");
            if ($outDT->lessThanOrEqualTo($inDT)) {
                $outDT->addDay();
            }

            $hoursWorked = round($inDT->diffInMinutes($outDT) / 60, 2);

            if ($isWeekend) {
                $status = 'present';
                $overtimeMinutes = (int) $inDT->diffInMinutes($outDT);
            } else {
                $expectedIn = Carbon::parse("{$dateStr} 08:00:00");
                if ($inDT->greaterThan($expectedIn)) {
                    $lateMinutes = (int) $expectedIn->diffInMinutes($inDT);
                }

                $expectedOut = Carbon::parse("{$dateStr} 17:00:00");
                if ($outDT->lessThan($expectedOut) && $outDT->isSameDay($inDT)) {
                    $undertimeMinutes = (int) $outDT->diffInMinutes($expectedOut);
                }

                if ($outDT->greaterThan($expectedOut)) {
                    $actualStart = $inDT->greaterThan($expectedOut) ? $inDT : $expectedOut;
                    $overtimeMinutes = (int) $actualStart->diffInMinutes($outDT);
                }

                // Late threshold: >= 5 minutes tardiness
                $status = $lateMinutes >= 5 ? 'late' : 'present';

                // If worked less than 5 hours on a weekday, classify as half day
                if ($hoursWorked < 5.0 && $hoursWorked > 0) {
                    $status = 'half_day';
                }
            }
        } elseif ($timeIn && !$timeOut) {
            $inDT = Carbon::parse("{$dateStr} {$timeIn}");
            if (!$isWeekend) {
                $expectedIn = Carbon::parse("{$dateStr} 08:00:00");
                if ($inDT->greaterThan($expectedIn)) {
                    $lateMinutes = (int) $expectedIn->diffInMinutes($inDT);
                }
                $status = $lateMinutes >= 5 ? 'late' : 'present';
            }

            // If it's a past date and time_out was never registered, mark as half day
            if ($date->isPast() && !$date->isToday()) {
                $status = 'half_day';
                $hoursWorked = 4.0;
            } else {
                // Currently working today
                $hoursWorked = 0;
            }
        } elseif (!$timeIn && $timeOut) {
            $status = 'half_day';
            $hoursWorked = 4.0;
        }

        // Cap overnight shift overtime at 4 hours (240 mins)
        if ($overtimeMinutes > 240) {
            $overtimeMinutes = 240;
        }

        $overtimeStatus = $overtimeMinutes > 0 ? 'pending' : 'none';

        // Check if existing record is on approved leave or official business
        $existing = AttendanceRecord::where('employee_id', $hrisEmployeeId)
            ->where('date', $dateStr)
            ->first();

        if ($existing && in_array($existing->status, ['leave', 'official_business']) && empty($existing->time_in)) {
            // Keep status if already approved leave/OB, but attach the recorded punches
            $status = $existing->status;
        }

        AttendanceRecord::updateOrCreate(
            [
                'employee_id' => $hrisEmployeeId,
                'date'        => $dateStr,
            ],
            [
                'time_in'           => $timeIn,
                'time_out'          => $timeOut,
                'hours_worked'      => $hoursWorked,
                'late_minutes'      => $lateMinutes,
                'undertime_minutes' => $undertimeMinutes,
                'status'            => $status,
                'source'            => 'rfid',
                'overtime_minutes'  => $overtimeMinutes,
                'overtime_status'   => $overtimeStatus,
            ]
        );

        return true;
    }

    /**
     * Map RFID employees to HRIS employees:
     * 1. Matches employee_number with employee_no
     * 2. Fallback: Matches by full name (first_name + last_name)
     *
     * @return array [ rfid_employee_id => hris_employee_id ]
     */
    public function buildEmployeeMap(): array
    {
        try {
            $rfidEmployees = DB::connection('rfid')->table('employees')->get();
            $hrisEmployees = Employee::where('is_active', true)->get();

            $map = [];

            // Index HRIS employees by employee_no (clean string)
            $hrisByNo = [];
            $hrisByName = [];

            foreach ($hrisEmployees as $hEmp) {
                if ($hEmp->employee_no) {
                    $cleanNo = $this->normalizeString($hEmp->employee_no);
                    $hrisByNo[$cleanNo] = $hEmp->id;
                }
                $cleanName = $this->normalizeString($hEmp->name);
                $hrisByName[$cleanName] = $hEmp->id;
            }

            foreach ($rfidEmployees as $rEmp) {
                $matchedHrisId = null;

                // 1. Try matching by employee_number
                if (!empty($rEmp->employee_number)) {
                    $cleanRfidNo = $this->normalizeString($rEmp->employee_number);
                    if (isset($hrisByNo[$cleanRfidNo])) {
                        $matchedHrisId = $hrisByNo[$cleanRfidNo];
                    }
                }

                // 2. Try matching by first_name + last_name
                if (!$matchedHrisId) {
                    $fullName1 = $this->normalizeString(($rEmp->first_name ?? '') . ' ' . ($rEmp->last_name ?? ''));
                    $fullName2 = $this->normalizeString(($rEmp->last_name ?? '') . ' ' . ($rEmp->first_name ?? ''));
                    $fullName3 = $this->normalizeString(($rEmp->last_name ?? '') . ', ' . ($rEmp->first_name ?? ''));

                    if (isset($hrisByName[$fullName1])) {
                        $matchedHrisId = $hrisByName[$fullName1];
                    } elseif (isset($hrisByName[$fullName2])) {
                        $matchedHrisId = $hrisByName[$fullName2];
                    } elseif (isset($hrisByName[$fullName3])) {
                        $matchedHrisId = $hrisByName[$fullName3];
                    } else {
                        // Partial substring match
                        foreach ($hrisByName as $hName => $hId) {
                            if (str_contains($hName, $this->normalizeString($rEmp->last_name ?? '')) &&
                                str_contains($hName, $this->normalizeString($rEmp->first_name ?? ''))) {
                                $matchedHrisId = $hId;
                                break;
                            }
                        }
                    }
                }

                if ($matchedHrisId) {
                    $map[$rEmp->id] = $matchedHrisId;
                }
            }

            return $map;
        } catch (\Throwable $e) {
            Log::error('Error building RFID employee map: ' . $e->getMessage());
            return [];
        }
    }

    protected function normalizeString(string $val): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $val));
    }
}
