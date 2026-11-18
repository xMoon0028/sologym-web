<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\AttendanceImport;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

use App\Services\SpreadsheetImportService;

class AttendanceController extends Controller
{
    public function __construct(protected SpreadsheetImportService $importService)
    {
    }

    public function index(Request $request)
    {
        $employees = Employee::where('is_active', true)->orderBy('name')->get();
        $selectedEmployee = null;

        // If month is not explicitly requested, pick latest month that has attendance records or current month
        $defaultMonth = AttendanceRecord::orderBy('date', 'desc')->value('date');
        $month = $request->filled('month')
            ? $request->month
            : ($defaultMonth ? \Carbon\Carbon::parse($defaultMonth)->format('Y-m') : now()->format('Y-m'));

        $startOfMonth = $month ? \Carbon\Carbon::parse($month)->startOfMonth()->toDateString() : null;
        $endOfMonth   = $month ? \Carbon\Carbon::parse($month)->endOfMonth()->toDateString() : null;

        $query = AttendanceRecord::with('employee')
            ->when($startOfMonth && $endOfMonth, function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('date', [$startOfMonth, $endOfMonth]);
            });

        if ($request->filled('employee_id')) {
            $selectedEmployee = Employee::find($request->employee_id);
        }

        if ($selectedEmployee && $startOfMonth && $endOfMonth) {
            // Auto-sync employee punches from the RFID database for this month
            try {
                $syncEnd = $month === now()->format('Y-m') ? now()->toDateString() : $endOfMonth;
                app(\App\Services\RfidAttendanceSyncService::class)->syncForEmployee($selectedEmployee, $startOfMonth, $syncEnd);
            } catch (\Throwable $e) {
                // Silently continue if RFID is unreachable
            }

            $startDateObj = \Carbon\Carbon::parse($startOfMonth);
            $endDateObj   = \Carbon\Carbon::parse($endOfMonth);

            $dbRecords = AttendanceRecord::with('employee')
                ->where('employee_id', $selectedEmployee->id)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->get()
                ->keyBy(fn($r) => $r->date->format('Y-m-d'));

            $approvedLeaves = \App\Models\LeaveRequest::where('employee_id', $selectedEmployee->id)
                ->where('status', 'approved')
                ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                      ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                      ->orWhere(fn($sq) => $sq->where('start_date', '<=', $startOfMonth)->where('end_date', '>=', $endOfMonth));
                })
                ->get();

            $calendarDays = collect();
            $today = now()->startOfDay();

            for ($date = $endDateObj->copy(); $date->greaterThanOrEqualTo($startDateObj); $date->subDay()) {
                $dateStr   = $date->format('Y-m-d');
                $isWeekend = $date->isWeekend();
                $isFuture  = $date->startOfDay()->greaterThan($today);

                if (isset($dbRecords[$dateStr])) {
                    $calendarDays->push($dbRecords[$dateStr]);
                } else {
                    $leaveMatch = $approvedLeaves->first(function ($l) use ($dateStr) {
                        return $dateStr >= $l->start_date->format('Y-m-d') && $dateStr <= $l->end_date->format('Y-m-d');
                    });

                    if ($leaveMatch) {
                        $status = 'leave';
                    } elseif ($isWeekend) {
                        $status = 'weekend';
                    } elseif ($isFuture) {
                        $status = 'upcoming';
                    } else {
                        $status = 'absent';
                    }

                    $calendarDays->push((object) [
                        'id'                => null,
                        'employee_id'       => $selectedEmployee->id,
                        'employee'          => $selectedEmployee,
                        'date'              => $date->copy(),
                        'time_in'           => null,
                        'time_out'          => null,
                        'hours_worked'      => 0,
                        'late_minutes'      => 0,
                        'undertime_minutes' => 0,
                        'overtime_minutes'  => 0,
                        'overtime_status'   => 'none',
                        'status'            => $status,
                        'source'            => 'unlogged',
                    ]);
                }
            }

            $presents           = $calendarDays->whereIn('status', ['present', 'late', 'half_day'])->count();
            $lates              = $calendarDays->where('status', 'late')->count();
            $absents            = $calendarDays->where('status', 'absent')->count();
            $leaves             = $calendarDays->where('status', 'leave')->count();
            $officialBusinesses = $calendarDays->where('status', 'official_business')->count();
            $halfDays           = $calendarDays->where('status', 'half_day')->count();
            $totalLateMins      = $calendarDays->sum('late_minutes');
            $totalHours         = $calendarDays->sum('hours_worked');
            $totalRecords       = $calendarDays->count();
            $pendingOvertimeCount = $calendarDays->where('overtime_status', 'pending')->count();

            $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
            $perPage = 35;
            $items = $calendarDays->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $records = new \Illuminate\Pagination\LengthAwarePaginator($items, $calendarDays->count(), $perPage, $currentPage, [
                'path'  => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]);
        } else {
            $query = AttendanceRecord::with('employee')
                ->when($startOfMonth && $endOfMonth, function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->whereBetween('date', [$startOfMonth, $endOfMonth]);
                });

            $records = $query->orderBy('date', 'desc')->orderBy('employee_id')->paginate(35)->withQueryString();

            // Calculate statistics for the current filtered view
            $statsQuery = AttendanceRecord::query()
                ->when($startOfMonth && $endOfMonth, function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->whereBetween('date', [$startOfMonth, $endOfMonth]);
                });

            $stats = (clone $statsQuery)->selectRaw("
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as presents,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as lates,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absents,
                SUM(CASE WHEN status = 'leave' THEN 1 ELSE 0 END) as leaves,
                SUM(CASE WHEN status = 'official_business' THEN 1 ELSE 0 END) as official_businesses,
                SUM(CASE WHEN status = 'half_day' THEN 1 ELSE 0 END) as half_days,
                COALESCE(SUM(late_minutes), 0) as total_late_mins,
                COALESCE(SUM(hours_worked), 0) as total_hours,
                COUNT(*) as total_records,
                SUM(CASE WHEN overtime_status = 'pending' AND overtime_minutes > 0 THEN 1 ELSE 0 END) as pending_overtimes
            ")->first();

            $presents = (int) ($stats->presents ?? 0);
            $lates = (int) ($stats->lates ?? 0);
            $absents = (int) ($stats->absents ?? 0);
            $leaves = (int) ($stats->leaves ?? 0);
            $officialBusinesses = (int) ($stats->official_businesses ?? 0);
            $halfDays = (int) ($stats->half_days ?? 0);
            $totalLateMins = (int) ($stats->total_late_mins ?? 0);
            $totalHours = (float) ($stats->total_hours ?? 0);
            $totalRecords = (int) ($stats->total_records ?? 0);
            $pendingOvertimeCount = (int) ($stats->pending_overtimes ?? 0);
        }

        return view('admin.attendance.index', compact(
            'employees',
            'selectedEmployee',
            'records',
            'month',
            'presents',
            'lates',
            'absents',
            'leaves',
            'officialBusinesses',
            'halfDays',
            'totalLateMins',
            'totalHours',
            'totalRecords',
            'pendingOvertimeCount'
        ));
    }

    public function import(Request $request)
    {
        $file = $this->importService->validateSpreadsheet($request);
        $import = $this->importService->importAttendance($file);

        $empCount = count($import->employeesProcessed);
        $periodMsg = '';
        if ($import->startDate && $import->endDate) {
            $periodMsg = " (" . date('M d, Y', strtotime($import->startDate)) . " to " . date('M d, Y', strtotime($import->endDate)) . ")";
        }
        $msg = "Imported {$import->imported} attendance records for {$empCount} employees{$periodMsg}.";
        if ($import->skipped) $msg .= " {$import->skipped} skipped.";
        if ($import->errors) $msg .= ' Errors: ' . implode('; ', array_slice($import->errors, 0, 5));

        $redirectParams = [];
        if ($import->startDate) {
            $redirectParams['month'] = date('Y-m', strtotime($import->startDate));
        }

        return redirect()->route('admin.attendance.index', $redirectParams)->with('success', $msg);
    }

    public function show(Employee $employee)
    {
        $records = AttendanceRecord::where('employee_id', $employee->id)
            ->orderBy('date', 'desc')->paginate(31);
        return view('admin.attendance.show', compact('employee', 'records'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late,half_day,holiday,leave,official_business,weekend',
            'late_minutes' => 'nullable|integer|min:0',
            'undertime_minutes' => 'nullable|integer|min:0',
            'overtime_minutes' => 'nullable|integer|min:0',
            'overtime_status' => 'nullable|in:none,pending,approved,rejected',
        ]);
        $data = $request->only(['time_in','time_out','hours_worked','late_minutes','undertime_minutes','overtime_minutes','overtime_status','status']) + ['source' => 'manual'];

        $emp = Employee::find($request->employee_id);
        if ($emp && $emp->hasOverdueNte($request->date) && !empty($data['time_in'])) {
            return back()->with('error', "Cannot record Time-In: Employee {$emp->name} has an active overdue Notice to Explain (NTE). Please attach the scanned copy of their NTE in Disciplinary Records to restore privileges.")->withInput();
        }

        $lateMins = (int)($data['late_minutes'] ?? 0);
        if (in_array($data['status'], ['present', 'late'])) {
            $data['status'] = $lateMins >= 5 ? 'late' : 'present';
        }

        // Calculate overtime_minutes if not provided and times are present
        if (!isset($data['overtime_minutes']) && !empty($data['time_in']) && !empty($data['time_out'])) {
            $d = \Carbon\Carbon::parse($request->date);
            $inDT = \Carbon\Carbon::parse("{$request->date} {$data['time_in']}");
            $outDT = \Carbon\Carbon::parse("{$request->date} {$data['time_out']}");
            if ($outDT->lessThanOrEqualTo($inDT)) $outDT->addDay();

            $otMins = 0;
            if ($d->isWeekend()) {
                $otMins = (int) $inDT->diffInMinutes($outDT);
            } else {
                $expectedOut = \Carbon\Carbon::parse("{$request->date} 17:00:00");
                if ($outDT->greaterThan($expectedOut)) {
                    $actualStart = $inDT->greaterThan($expectedOut) ? $inDT : $expectedOut;
                    $otMins = (int) $actualStart->diffInMinutes($outDT);
                }
            }
            if ($outDT->diffInDays($inDT) >= 1 && $otMins > 240) $otMins = 240;
            $data['overtime_minutes'] = $otMins;
            $data['overtime_status'] = $otMins > 0 ? 'pending' : 'none';
        }

        $record = AttendanceRecord::updateOrCreate(
            ['employee_id' => $request->employee_id, 'date' => $request->date],
            $data
        );

        if (($record->overtime_minutes ?? 0) > 0) {
            app(\App\Services\TimeCreditService::class)->processRecord($record);
        }

        app(\App\Services\NotificationService::class)->notifyAttendanceUpdated($record->fresh());

        return back()->with('success', 'Attendance record saved.');
    }

    public function update(Request $request, AttendanceRecord $record)
    {
        $validated = $request->validate([
            'status' => 'required|in:present,absent,late,half_day,holiday,leave,official_business,weekend',
            'time_in' => 'nullable|string',
            'time_out' => 'nullable|string',
            'hours_worked' => 'nullable|numeric|min:0|max:24',
            'late_minutes' => 'nullable|integer|min:0',
            'undertime_minutes' => 'nullable|integer|min:0',
            'overtime_minutes' => 'nullable|integer|min:0',
            'overtime_status' => 'nullable|in:none,pending,approved,rejected',
        ]);

        if ($record->employee && $record->employee->hasOverdueNte($record->date->toDateString()) && !empty($validated['time_in']) && in_array($validated['status'], ['present', 'late', 'half_day'])) {
            return back()->with('error', "Cannot record Time-In: Employee {$record->employee->name} has an active overdue Notice to Explain (NTE). Attach the scanned NTE in the Disciplinary module to restore access.")->withInput();
        }

        $updateData = [
            'status' => $validated['status'],
        ];

        if ($validated['status'] === 'official_business') {
            $updateData['late_minutes'] = $validated['late_minutes'] ?? 0;
            $updateData['undertime_minutes'] = $validated['undertime_minutes'] ?? 0;
            $updateData['time_in'] = !empty($validated['time_in']) ? $validated['time_in'] : null;
            $updateData['time_out'] = !empty($validated['time_out']) ? $validated['time_out'] : null;
            $updateData['hours_worked'] = !empty($validated['hours_worked']) ? (float)$validated['hours_worked'] : 8.0;
        } elseif ($validated['status'] === 'leave') {
            $updateData['late_minutes'] = $validated['late_minutes'] ?? 0;
            $updateData['undertime_minutes'] = $validated['undertime_minutes'] ?? 0;
            $updateData['time_in'] = !empty($validated['time_in']) ? $validated['time_in'] : null;
            $updateData['time_out'] = !empty($validated['time_out']) ? $validated['time_out'] : null;
            $updateData['hours_worked'] = isset($validated['hours_worked']) ? (float)$validated['hours_worked'] : 0;
        } elseif ($validated['status'] === 'absent') {
            $updateData['time_in'] = null;
            $updateData['time_out'] = null;
            $updateData['hours_worked'] = 0;
            $updateData['late_minutes'] = 0;
            $updateData['undertime_minutes'] = 0;
            $updateData['overtime_minutes'] = 0;
            $updateData['overtime_status'] = 'none';
        } elseif ($validated['status'] === 'holiday') {
            $updateData['late_minutes'] = 0;
            $updateData['undertime_minutes'] = 0;
            if (isset($validated['hours_worked'])) {
                $updateData['hours_worked'] = (float)$validated['hours_worked'];
            }
            if (isset($validated['time_in'])) {
                $updateData['time_in'] = $validated['time_in'] ?: null;
            }
            if (isset($validated['time_out'])) {
                $updateData['time_out'] = $validated['time_out'] ?: null;
            }
        } else {
            // present, late, half_day, weekend
            if (isset($validated['time_in'])) {
                $updateData['time_in'] = $validated['time_in'] ?: null;
            }
            if (isset($validated['time_out'])) {
                $updateData['time_out'] = $validated['time_out'] ?: null;
            }
            if (isset($validated['late_minutes'])) {
                $updateData['late_minutes'] = (int)$validated['late_minutes'];
            }
            if (isset($validated['undertime_minutes'])) {
                $updateData['undertime_minutes'] = (int)$validated['undertime_minutes'];
            }
            if (isset($validated['hours_worked'])) {
                $updateData['hours_worked'] = (float)$validated['hours_worked'];
            }

            // Enforce late threshold rule on present/late statuses
            $lateMinutes = $updateData['late_minutes'] ?? $record->late_minutes ?? 0;
            if (in_array($updateData['status'], ['present', 'late'])) {
                $updateData['status'] = $lateMinutes >= 5 ? 'late' : 'present';
            }
        }

        if (isset($validated['overtime_minutes'])) {
            $updateData['overtime_minutes'] = (int)$validated['overtime_minutes'];
        }
        if (isset($validated['overtime_status'])) {
            $updateData['overtime_status'] = $validated['overtime_status'];
        }

        $record->update($updateData);

        // Process overtime and sync with TimeCreditLedger
        $timeCreditService = app(\App\Services\TimeCreditService::class);
        if ($record->overtime_status === 'approved') {
            $timeCreditService->approveAttendanceOvertime($record);
        } elseif ($record->overtime_status === 'rejected') {
            $timeCreditService->rejectAttendanceOvertime($record);
        } elseif (($record->overtime_minutes ?? 0) > 0) {
            $timeCreditService->processRecord($record);
        }

        // Notify staff of attendance modification
        app(\App\Services\NotificationService::class)->notifyAttendanceUpdated($record->fresh());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Attendance status updated successfully.',
                'record' => $record->fresh(),
            ]);
        }

        $statusLabel = strtoupper(str_replace('_', ' ', $record->status));
        return back()->with('success', "Attendance status updated to '{$statusLabel}' successfully.");
    }

    public function approveOvertime(AttendanceRecord $record)
    {
        app(\App\Services\TimeCreditService::class)->approveAttendanceOvertime($record);
        return back()->with('success', "Overtime of {$record->overtime_minutes} minutes approved as valid time credits for {$record->employee->name}.");
    }

    public function rejectOvertime(Request $request, AttendanceRecord $record)
    {
        $reason = $request->input('reason', 'Rejected by HR/Admin');
        app(\App\Services\TimeCreditService::class)->rejectAttendanceOvertime($record, $reason);
        return back()->with('success', "Overtime of {$record->overtime_minutes} minutes rejected for {$record->employee->name}. It will not record as time credits.");
    }

    public function approveAllOvertime()
    {
        $records = AttendanceRecord::where('overtime_status', 'pending')->where('overtime_minutes', '>', 0)->get();
        $service = app(\App\Services\TimeCreditService::class);
        $count = 0;
        foreach ($records as $rec) {
            $service->approveAttendanceOvertime($rec);
            $count++;
        }
        return back()->with('success', "Approved {$count} pending overtime records as valid time credits.");
    }

    public function syncRfid(Request $request, \App\Services\RfidAttendanceSyncService $syncService)
    {
        $employeeId = $request->filled('employee_id') ? (int) $request->employee_id : null;

        if ($request->boolean('all')) {
            $count = $syncService->syncAll($employeeId);
            return back()->with('success', "Successfully synchronized {$count} total attendance records from the RFID database across all months.");
        }

        $month = $request->input('month', now()->format('Y-m'));
        $startDate = \Carbon\Carbon::parse($month)->startOfMonth()->toDateString();
        $endDate   = \Carbon\Carbon::parse($month)->endOfMonth()->toDateString();

        if ($month === now()->format('Y-m')) {
            $endDate = now()->toDateString();
        }

        $count = $syncService->syncDateRange($startDate, $endDate, $employeeId);

        return back()->with('success', "Successfully synced {$count} attendance records from the RFID database for " . \Carbon\Carbon::parse($month)->format('F Y') . ".");
    }

    public function destroy(AttendanceRecord $record)
    {
        $record->delete();
        return back()->with('success', 'Record deleted.');
    }
}
