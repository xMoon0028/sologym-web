<?php

namespace App\Imports;

use App\Models\AttendanceRecord;
use App\Models\Department;
use App\Models\Employee;
use App\Services\TimeCreditService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class AttendanceImport implements ToCollection, SkipsEmptyRows
{
    public array $errors = [];
    public int $imported = 0;
    public int $skipped = 0;
    public array $employeesProcessed = [];
    public ?string $startDate = null;
    public ?string $endDate = null;

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            return;
        }

        // 1. Detect if this is a Horizontal Matrix format or Vertical Row format
        $formatInfo = $this->detectFormat($rows);

        if ($formatInfo['type'] === 'matrix') {
            $this->processMatrix($rows, $formatInfo);
        } else {
            $this->processVertical($rows, $formatInfo['headerRowIdx'] ?? 0);
        }
    }

    /**
     * Detect whether the file is horizontal matrix or vertical row-by-row.
     */
    /**
     * Detect whether the file is horizontal matrix or vertical row-by-row.
     */
    protected function detectFormat(Collection $rows): array
    {
        $maxScan = min(10, count($rows));

        for ($i = 0; $i < $maxScan; $i++) {
            $row = $rows[$i];
            $rowArr = is_array($row) ? $row : $row->toArray();

            $dateColCount = 0;
            foreach ($rowArr as $cell) {
                if ($this->isDateHeader((string) $cell)) {
                    $dateColCount++;
                }
            }

            // If 2 or more date headers are on the same row, it's horizontal matrix
            if ($dateColCount >= 2) {
                return [
                    'type' => 'matrix',
                    'headerRowIdx' => $i,
                ];
            }
        }

        // Check for vertical headers
        for ($i = 0; $i < $maxScan; $i++) {
            $row = $rows[$i];
            $rowArr = is_array($row) ? $row : $row->toArray();
            $rowStr = strtolower(implode(' ', array_filter($rowArr, fn($v) => is_string($v) || is_numeric($v))));

            if (str_contains($rowStr, 'date') && (str_contains($rowStr, 'employee') || str_contains($rowStr, 'name') || str_contains($rowStr, 'time_in') || str_contains($rowStr, 'in'))) {
                return [
                    'type' => 'vertical',
                    'headerRowIdx' => $i,
                ];
            }
        }

        return [
            'type' => 'vertical',
            'headerRowIdx' => 0,
        ];
    }

    /**
     * Check if a cell string represents a date header (e.g. "21-Aug", "1-Sep", "Aug 21\nFri", "2026-08-21").
     */
    protected function isDateHeader(mixed $val): bool
    {
        if ($val instanceof \DateTimeInterface) return true;
        $val = trim((string) $val);
        if (empty($val)) return false;

        $clean = trim(preg_replace('/[\r\n\t]+/', ' ', strip_tags($val)));
        $clean = trim(preg_replace('/\s+/', ' ', $clean));
        if (empty($clean)) return false;

        $monthPattern = '(?:Jan(?:uary)?|Feb(?:ruary)?|Mar(?:ch)?|Apr(?:il)?|May|Jun(?:e)?|Jul(?:y)?|Aug(?:ust)?|Sep(?:tember)?|Oct(?:ober)?|Nov(?:ember)?|Dec(?:ember)?)';
        $dayOfWeekPattern = '(?:Mon(?:day)?|Tue(?:sday)?|Wed(?:nesday)?|Thu(?:rsday)?|Fri(?:day)?|Sat(?:urday)?|Sun(?:day)?)';

        // 1. Month then Day (e.g. "Aug 21", "Aug 21 Fri", "Aug 21, Fri", "Aug-21", "August 21, 2026")
        if (preg_match('/^' . $monthPattern . '[- \/.]\d{1,2}(?:st|nd|rd|th)?(?:[- \/,]+(?:\d{2,4}|' . $dayOfWeekPattern . '))?$/i', $clean)) {
            return true;
        }

        // 2. Day then Month (e.g. "21-Aug", "21 Aug", "21 Aug Fri", "21/Aug", "21-Aug-2026", "21 Aug 2026")
        if (preg_match('/^\d{1,2}(?:st|nd|rd|th)?[- \/.]' . $monthPattern . '(?:[- \/,]+(?:\d{2,4}|' . $dayOfWeekPattern . '))?$/i', $clean)) {
            return true;
        }

        // 3. Numeric ISO or standard formats (e.g. "2026-08-21", "2026/08/21", "08/21/2026", "21/08/2026", "08/21", "8/21")
        if (preg_match('/^\d{4}[-\/]\d{1,2}[-\/]\d{1,2}$/', $clean) || preg_match('/^\d{1,2}\/\d{1,2}(?:\/\d{2,4})?$/', $clean)) {
            return true;
        }

        // 4. Excel numeric serial date (e.g. 45525)
        if (is_numeric($clean) && (int)$clean >= 40000 && (int)$clean <= 60000) {
            return true;
        }

        return false;
    }

    /**
     * Process horizontal matrix format.
     */
    protected function processMatrix(Collection $rows, array $formatInfo): void
    {
        $headerRowIdx = $formatInfo['headerRowIdx'];
        $headerRow = $rows[$headerRowIdx];
        $headerRowArr = is_array($headerRow) ? $headerRow : $headerRow->toArray();

        // 1. Detect year from title / preceding rows
        $combinedPreText = '';
        for ($i = 0; $i <= min(5, count($rows) - 1); $i++) {
            $rArr = is_array($rows[$i]) ? $rows[$i] : $rows[$i]->toArray();
            $combinedPreText .= ' ' . implode(' ', array_filter($rArr, fn($v) => is_string($v) || is_numeric($v)));
        }
        preg_match_all('/\b(20\d\d)\b/', $combinedPreText, $yearMatches);
        $year = !empty($yearMatches[1]) ? (int) $yearMatches[1][0] : Carbon::now()->year;

        // 2. Identify column indices
        $empNoCol = null;
        $empNameCol = null;
        $dateColumns = []; // colIndex => 'YYYY-MM-DD'

        foreach ($headerRowArr as $colIdx => $colVal) {
            $colStr = trim((string) $colVal);
            if (empty($colStr)) continue;

            $cleanHeader = trim(preg_replace('/[\r\n\t]+/', ' ', strip_tags($colStr)));
            $cleanHeader = trim(preg_replace('/\s+/', ' ', $cleanHeader));

            if (preg_match('/(?:employee\s*(?:no|num|id)|id\s*number)/i', $cleanHeader)) {
                $empNoCol = $colIdx;
                continue;
            }
            if (preg_match('/(?:employee\s*name|staff\s*name|full\s*name|^name$)/i', $cleanHeader)) {
                $empNameCol = $colIdx;
                continue;
            }
            if ($this->isDateHeader($colStr)) {
                $parsedDate = $this->parseDateHeader($colStr, $year);
                if ($parsedDate) {
                    $dateColumns[$colIdx] = $parsedDate;
                }
            }
        }

        if (!empty($dateColumns)) {
            $sortedDates = array_values($dateColumns);
            sort($sortedDates);
            $this->startDate = $sortedDates[0];
            $this->endDate = end($sortedDates);
        }

        if ($empNoCol === null) $empNoCol = 0;
        if ($empNameCol === null) $empNameCol = 1;

        // 3. Process employee rows
        $totalRows = count($rows);
        for ($i = $headerRowIdx + 1; $i < $totalRows; $i++) {
            $row = $rows[$i];
            $rowArr = is_array($row) ? $row : $row->toArray();

            // Skip completely empty rows
            if (empty(array_filter($rowArr, fn($v) => !is_null($v) && trim((string)$v) !== ''))) {
                continue;
            }

            // Skip secondary header rows like days of the week (Fri, Sat, Sun...)
            if ($this->isDayOfWeekRow($rowArr)) {
                continue;
            }

            $empNoVal = trim((string)($rowArr[$empNoCol] ?? ''));
            $empNameVal = trim((string)($rowArr[$empNameCol] ?? ''));

            // Skip if both identifier cells are empty or contain summary keywords
            if (empty($empNoVal) && empty($empNameVal)) {
                continue;
            }
            if (preg_match('/(?:total|summary|prepared|approved|checked|noted)/i', $empNoVal . ' ' . $empNameVal)) {
                continue;
            }

            // Resolve or auto-create employee
            $employee = $this->resolveEmployee($empNoVal, $empNameVal);
            if (!$employee) {
                $this->skipped++;
                $this->errors[] = "Unable to resolve employee: {$empNoVal} - {$empNameVal}";
                continue;
            }

            $this->employeesProcessed[$employee->id] = $employee->name;

            // Process each date column for this employee
            foreach ($dateColumns as $colIdx => $dateStr) {
                $cellVal = trim((string)($rowArr[$colIdx] ?? ''));
                $parsed = $this->parseAttendanceCell($cellVal, $dateStr);

                try {
                    $record = AttendanceRecord::updateOrCreate(
                        ['employee_id' => $employee->id, 'date' => $dateStr],
                        [
                            'time_in'           => $parsed['time_in'],
                            'time_out'          => $parsed['time_out'],
                            'hours_worked'      => $parsed['hours_worked'],
                            'late_minutes'      => $parsed['late_minutes'],
                            'undertime_minutes' => $parsed['undertime_minutes'],
                            'overtime_minutes'  => $parsed['overtime_minutes'] ?? 0,
                            'overtime_status'   => $parsed['overtime_status'] ?? 'none',
                            'status'            => $parsed['status'],
                            'source'            => 'import',
                        ]
                    );

                    $this->imported++;

                    // Auto-accrue time credits for weekend / overtime work (pending HR/Admin approval)
                    app(TimeCreditService::class)->processRecord($record);
                } catch (\Exception $e) {
                    $this->errors[] = "Error saving {$employee->name} on {$dateStr}: " . $e->getMessage();
                }
            }
        }
    }

    /**
     * Check if a row is purely days of the week (Fri, Sat, Sun, etc.).
     */
    protected function isDayOfWeekRow(array $row): bool
    {
        $nonEmpty = array_values(array_filter(array_map(fn($v) => trim((string)$v), $row), fn($v) => $v !== ''));
        if (empty($nonEmpty)) return false;

        $dayCount = 0;
        foreach ($nonEmpty as $val) {
            if (preg_match('/^(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun|Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)$/i', $val)) {
                $dayCount++;
            }
        }

        return $dayCount >= 2 && ($dayCount / count($nonEmpty)) >= 0.4;
    }

    /**
     * Parse date header string with year into Y-m-d.
     */
    protected function parseDateHeader(mixed $val, int $year): ?string
    {
        if ($val instanceof \DateTimeInterface) {
            return Carbon::instance($val)->format('Y-m-d');
        }
        $val = trim((string) $val);
        if (empty($val)) return null;

        $clean = trim(preg_replace('/[\r\n\t]+/', ' ', strip_tags($val)));
        $clean = trim(preg_replace('/\s+/', ' ', $clean));

        // Excel numeric serial date (e.g. 45525)
        if (is_numeric($clean) && (int)$clean >= 40000 && (int)$clean <= 60000) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$clean))->format('Y-m-d');
            } catch (\Throwable $e) {}
        }

        // If it already has a 4-digit year, parse directly
        if (preg_match('/\b(20\d\d)\b/', $clean)) {
            try {
                return Carbon::parse($clean)->format('Y-m-d');
            } catch (\Throwable $e) {}
        }

        // If it has day of week (e.g. "Aug 21 Fri", "21 Aug Fri"), strip day of week before appending year
        $dayOfWeekPattern = '/\b(?:Mon(?:day)?|Tue(?:sday)?|Wed(?:nesday)?|Thu(?:rsday)?|Fri(?:day)?|Sat(?:urday)?|Sun(?:day)?)\b/i';
        $cleanedNoDow = trim(preg_replace($dayOfWeekPattern, '', $clean));
        $cleanedNoDow = trim(preg_replace('/[,\s]+$/', '', $cleanedNoDow));

        try {
            return Carbon::parse("{$cleanedNoDow} {$year}")->format('Y-m-d');
        } catch (\Throwable $e) {
            try {
                return Carbon::parse("{$clean} {$year}")->format('Y-m-d');
            } catch (\Throwable $e2) {
                return null;
            }
        }
    }

    /**
     * Find existing employee by number or name, or auto-create new employee.
     */
    protected function resolveEmployee(string $empNo, string $empName): ?Employee
    {
        $employee = null;

        if (!empty($empNo)) {
            $cleanNo = str_replace(' ', '', $empNo);
            $employee = Employee::where('employee_no', $empNo)
                ->orWhereRaw("REPLACE(employee_no, ' ', '') = ?", [$cleanNo])
                ->first();
        }

        if (!$employee && !empty($empName)) {
            $employee = Employee::where('name', $empName)->first();
        }

        if (!$employee && !empty($empName)) {
            $employee = Employee::whereRaw('LOWER(name) = ?', [strtolower(trim($empName))])->first();
        }

        if (!$employee && !empty($empName)) {
            $parts = array_values(array_filter(array_map('trim', explode(',', $empName))));
            if (count($parts) >= 2) {
                $employee = Employee::where('name', 'like', "%{$parts[0]}%")
                    ->where('name', 'like', "%{$parts[1]}%")
                    ->first();
            } else {
                $employee = Employee::where('name', 'like', "%{$empName}%")->first();
            }
        }

        // Auto-create employee if not found
        if (!$employee && (!empty($empNo) || !empty($empName))) {
            $defaultDept = Department::first();
            $employee = Employee::create([
                'employee_no'       => !empty($empNo) ? $empNo : null,
                'name'              => !empty($empName) ? $empName : ('Employee ' . $empNo),
                'department_id'     => $defaultDept?->id,
                'employment_status' => 'Regular',
                'is_active'         => true,
            ]);
        }

        return $employee;
    }

    /**
     * Parse individual attendance cell (e.g. "12:00 PM - 05:07 PM", "-", "Weekend", "06:18 AM - --").
     */
    protected function parseAttendanceCell(string $val, string $dateStr): array
    {
        $val = trim(preg_replace('/[\r\n\t]+/', ' ', strip_tags($val)));
        $val = trim(preg_replace('/\s+/', ' ', $val));
        $d = Carbon::parse($dateStr);
        $isWeekend = $d->isWeekend();

        // Absent or empty
        if ($val === '' || $val === '-' || strcasecmp($val, 'absent') === 0) {
            return [
                'status'            => $isWeekend ? 'weekend' : 'absent',
                'time_in'           => null,
                'time_out'          => null,
                'hours_worked'      => 0,
                'late_minutes'      => 0,
                'undertime_minutes' => 0,
                'overtime_minutes'  => 0,
                'overtime_status'   => 'none',
            ];
        }

        if (strcasecmp($val, 'weekend') === 0) {
            return [
                'status'            => 'weekend',
                'time_in'           => null,
                'time_out'          => null,
                'hours_worked'      => 0,
                'late_minutes'      => 0,
                'undertime_minutes' => 0,
                'overtime_minutes'  => 0,
                'overtime_status'   => 'none',
            ];
        }

        if (strcasecmp($val, 'holiday') === 0) {
            return [
                'status'            => 'holiday',
                'time_in'           => null,
                'time_out'          => null,
                'hours_worked'      => 0,
                'late_minutes'      => 0,
                'undertime_minutes' => 0,
                'overtime_minutes'  => 0,
                'overtime_status'   => 'none',
            ];
        }

        if (strcasecmp($val, 'leave') === 0) {
            return [
                'status'            => 'leave',
                'time_in'           => null,
                'time_out'          => null,
                'hours_worked'      => 0,
                'late_minutes'      => 0,
                'undertime_minutes' => 0,
                'overtime_minutes'  => 0,
                'overtime_status'   => 'none',
            ];
        }

        // Time range: "07:58 AM - 05:06 PM" or "06:18 AM - --"
        if (preg_match('/(\d{1,2}:\d{2}(?:\s*[AP]M)?)\s*-\s*(\d{1,2}:\d{2}(?:\s*[AP]M)?|--)/i', $val, $m)) {
            $inStr = trim($m[1]);
            $outStr = trim($m[2]);

            try {
                $timeIn = Carbon::parse($inStr)->format('H:i:s');
            } catch (\Exception $e) {
                $timeIn = null;
            }

            // Half day / missing time out
            if ($outStr === '--' || $outStr === '' || empty($outStr)) {
                $late = 0;
                if ($timeIn && !$isWeekend) {
                    $expectedIn = Carbon::parse("{$dateStr} 08:00:00");
                    $inDT = Carbon::parse("{$dateStr} {$timeIn}");
                    if ($inDT->greaterThan($expectedIn)) {
                        $late = (int) $expectedIn->diffInMinutes($inDT);
                    }
                }
                return [
                    'status'            => 'half_day',
                    'time_in'           => $timeIn,
                    'time_out'          => null,
                    'hours_worked'      => 4.00,
                    'late_minutes'      => $late,
                    'undertime_minutes' => 0,
                    'overtime_minutes'  => 0,
                    'overtime_status'   => 'none',
                ];
            }

            try {
                $timeOut = Carbon::parse($outStr)->format('H:i:s');
            } catch (\Exception $e) {
                $timeOut = null;
            }

            if ($timeIn && $timeOut) {
                $inDT = Carbon::parse("{$dateStr} {$timeIn}");
                $outDT = Carbon::parse("{$dateStr} {$timeOut}");
                if ($outDT->lessThanOrEqualTo($inDT)) {
                    $outDT->addDay();
                }
                $hoursWorked = round($inDT->diffInMinutes($outDT) / 60, 2);

                $lateMinutes = 0;
                $undertimeMinutes = 0;
                $overtimeMinutes = 0;

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
                    // Business rule: >= 5 minutes tardiness counts as 'late' status; < 5 minutes remains 'present'
                    // In both cases, late_minutes is tracked and deducted from salary during payroll
                    $status = $lateMinutes >= 5 ? 'late' : 'present';
                }

                // Cap overnight shifts at 4 hours (240 mins)
                if ($outDT->diffInDays($inDT) >= 1 && $overtimeMinutes > 240) {
                    $overtimeMinutes = 240;
                }

                $overtimeStatus = $overtimeMinutes > 0 ? 'pending' : 'none';

                return [
                    'status'            => $status,
                    'time_in'           => $timeIn,
                    'time_out'          => $timeOut,
                    'hours_worked'      => $hoursWorked,
                    'late_minutes'      => $lateMinutes,
                    'undertime_minutes' => $undertimeMinutes,
                    'overtime_minutes'  => $overtimeMinutes,
                    'overtime_status'   => $overtimeStatus,
                ];
            }
        }

        // Single time: "07:58 AM"
        if (preg_match('/^\d{1,2}:\d{2}(?:\s*[AP]M)?$/i', $val)) {
            try {
                $timeIn = Carbon::parse($val)->format('H:i:s');
                return [
                    'status'            => 'half_day',
                    'time_in'           => $timeIn,
                    'time_out'          => null,
                    'hours_worked'      => 4.00,
                    'late_minutes'      => 0,
                    'undertime_minutes' => 0,
                    'overtime_minutes'  => 0,
                    'overtime_status'   => 'none',
                ];
            } catch (\Exception $e) {}
        }

        return [
            'status'            => 'present',
            'time_in'           => null,
            'time_out'          => null,
            'hours_worked'      => 0,
            'late_minutes'      => 0,
            'undertime_minutes' => 0,
            'overtime_minutes'  => 0,
            'overtime_status'   => 'none',
        ];
    }

    /**
     * Process vertical row-by-row format.
     */
    protected function processVertical(Collection $rows, int $headerRowIdx): void
    {
        $headerRow = array_map(fn($v) => strtolower(trim((string)$v)), $rows[$headerRowIdx]->toArray());
        $colMap = [];
        foreach ($headerRow as $idx => $name) {
            $clean = strtolower(preg_replace('/[^a-z0-9_]/', '', str_replace([' ', '-'], '_', $name)));
            $colMap[$clean] = $idx;
        }

        $empNoIdx = $colMap['employee_no'] ?? $colMap['id_number'] ?? $colMap['employee_id'] ?? null;
        $empNameIdx = $colMap['employee_name'] ?? $colMap['name'] ?? null;
        $dateIdx = $colMap['date'] ?? $colMap['attendance_date'] ?? null;
        $timeInIdx = $colMap['time_in'] ?? $colMap['in'] ?? null;
        $timeOutIdx = $colMap['time_out'] ?? $colMap['out'] ?? null;
        $statusIdx = $colMap['status'] ?? null;
        $lateIdx = $colMap['late_minutes'] ?? $colMap['late'] ?? null;
        $undertimeIdx = $colMap['undertime_minutes'] ?? $colMap['undertime'] ?? null;
        $hoursIdx = $colMap['hours_worked'] ?? $colMap['hours'] ?? null;

        $totalRows = count($rows);
        for ($i = $headerRowIdx + 1; $i < $totalRows; $i++) {
            $row = $rows[$i]->toArray();
            if (empty(array_filter($row, fn($v) => !is_null($v) && trim((string)$v) !== ''))) continue;

            $empNo = $empNoIdx !== null ? trim((string)($row[$empNoIdx] ?? '')) : '';
            $empName = $empNameIdx !== null ? trim((string)($row[$empNameIdx] ?? '')) : '';
            $dateRaw = $dateIdx !== null ? trim((string)($row[$dateIdx] ?? '')) : '';

            if (empty($dateRaw)) {
                $this->skipped++;
                continue;
            }

            try {
                $date = Carbon::parse($dateRaw)->format('Y-m-d');
            } catch (\Exception $e) {
                $this->skipped++;
                continue;
            }

            $employee = $this->resolveEmployee($empNo, $empName);
            if (!$employee) {
                $this->skipped++;
                $this->errors[] = "Employee not found: {$empName} {$empNo}";
                continue;
            }

            $this->employeesProcessed[$employee->id] = $employee->name;

            $timeIn = ($timeInIdx !== null && !empty($row[$timeInIdx])) ? Carbon::parse($row[$timeInIdx])->format('H:i:s') : null;
            $timeOut = ($timeOutIdx !== null && !empty($row[$timeOutIdx])) ? Carbon::parse($row[$timeOutIdx])->format('H:i:s') : null;

            $hoursWorked = 0;
            if ($hoursIdx !== null && isset($row[$hoursIdx]) && is_numeric($row[$hoursIdx])) {
                $hoursWorked = (float) $row[$hoursIdx];
            } elseif ($timeIn && $timeOut) {
                $inDT = Carbon::parse("{$date} {$timeIn}");
                $outDT = Carbon::parse("{$date} {$timeOut}");
                if ($outDT->lessThanOrEqualTo($inDT)) $outDT->addDay();
                $hoursWorked = round($inDT->diffInMinutes($outDT) / 60, 2);
            }

            $lateMinutes = ($lateIdx !== null && is_numeric($row[$lateIdx])) ? (int)$row[$lateIdx] : 0;
            $undertimeMinutes = ($undertimeIdx !== null && is_numeric($row[$undertimeIdx])) ? (int)$row[$undertimeIdx] : 0;
            $status = ($statusIdx !== null && !empty($row[$statusIdx])) ? strtolower(trim($row[$statusIdx])) : ($lateMinutes >= 5 ? 'late' : 'present');

            $overtimeMinutes = 0;
            if ($timeIn && $timeOut) {
                $inDT = Carbon::parse("{$date} {$timeIn}");
                $outDT = Carbon::parse("{$date} {$timeOut}");
                if ($outDT->lessThanOrEqualTo($inDT)) $outDT->addDay();
                $d = Carbon::parse($date);
                if ($d->isWeekend()) {
                    $overtimeMinutes = (int) $inDT->diffInMinutes($outDT);
                } else {
                    $expectedOut = Carbon::parse("{$date} 17:00:00");
                    if ($outDT->greaterThan($expectedOut)) {
                        $actualStart = $inDT->greaterThan($expectedOut) ? $inDT : $expectedOut;
                        $overtimeMinutes = (int) $actualStart->diffInMinutes($outDT);
                    }
                }
                if ($outDT->diffInDays($inDT) >= 1 && $overtimeMinutes > 240) {
                    $overtimeMinutes = 240;
                }
            }
            $overtimeStatus = $overtimeMinutes > 0 ? 'pending' : 'none';

            try {
                $record = AttendanceRecord::updateOrCreate(
                    ['employee_id' => $employee->id, 'date' => $date],
                    [
                        'time_in'           => $timeIn,
                        'time_out'          => $timeOut,
                        'hours_worked'      => $hoursWorked,
                        'late_minutes'      => $lateMinutes,
                        'undertime_minutes' => $undertimeMinutes,
                        'overtime_minutes'  => $overtimeMinutes,
                        'overtime_status'   => $overtimeStatus,
                        'status'            => $status,
                        'source'            => 'import',
                    ]
                );

                $this->imported++;
                app(TimeCreditService::class)->processRecord($record);
            } catch (\Exception $e) {
                $this->errors[] = "Row error: " . $e->getMessage();
            }
        }
    }
}
