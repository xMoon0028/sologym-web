<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Services\RfidAttendanceSyncService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeeAttendanceController extends Controller
{
    public function index(Request $request, RfidAttendanceSyncService $syncService)
    {
        $employee = auth()->user()->employee;
        if (!$employee) return view('portal.no-profile');

        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $startOfMonth = Carbon::parse($selectedMonth)->startOfMonth();
        $endOfMonth   = Carbon::parse($selectedMonth)->endOfMonth();

        // Sync punches from the RFID attendance database for the selected month in real time
        try {
            $syncEndDate = $selectedMonth === now()->format('Y-m') ? now()->toDateString() : $endOfMonth->toDateString();
            $syncService->syncForEmployee($employee, $startOfMonth->toDateString(), $syncEndDate);
        } catch (\Throwable $e) {
            // Silently continue if RFID database is unreachable so portal page still loads
        }

        // 1. Fetch existing attendance records from database for this month
        $dbRecords = AttendanceRecord::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(fn($r) => $r->date->format('Y-m-d'));

        // 2. Fetch approved leaves for this employee within this month
        $approvedLeaves = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('start_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                  ->orWhereBetween('end_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                  ->orWhere(fn($sq) => $sq->where('start_date', '<=', $startOfMonth->toDateString())->where('end_date', '>=', $endOfMonth->toDateString()));
            })
            ->get();

        $calendarDays = collect();
        $today = now()->startOfDay();

        // 3. Build a complete calendar record for every single day of the month (descending order)
        for ($date = $endOfMonth->copy(); $date->greaterThanOrEqualTo($startOfMonth); $date->subDay()) {
            $dateStr   = $date->format('Y-m-d');
            $isWeekend = $date->isWeekend();
            $isFuture  = $date->startOfDay()->greaterThan($today);

            if (isset($dbRecords[$dateStr])) {
                $calendarDays->push($dbRecords[$dateStr]);
            } else {
                // Check if covered by an approved leave
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
                    // Weekday in the past with no log -> Absent / No Log
                    $status = 'absent';
                }

                $virtualRecord = (object) [
                    'id'                => null,
                    'employee_id'       => $employee->id,
                    'date'              => $date->copy(),
                    'time_in'           => null,
                    'time_out'          => null,
                    'hours_worked'      => 0,
                    'late_minutes'      => 0,
                    'undertime_minutes' => 0,
                    'status'            => $status,
                    'source'            => null,
                ];

                $calendarDays->push($virtualRecord);
            }
        }

        // Summary counts
        $summary = [
            'present'           => $calendarDays->whereIn('status', ['present', 'late', 'half_day'])->count(),
            'official_business' => $calendarDays->where('status', 'official_business')->count(),
            'leave'             => $calendarDays->where('status', 'leave')->count(),
            'late'              => $calendarDays->where('status', 'late')->count(),
            'absent'            => $calendarDays->where('status', 'absent')->count(),
        ];

        // Paginate full month (31 records per page so entire month fits on 1 page)
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 31;
        $items = $calendarDays->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $records = new LengthAwarePaginator($items, $calendarDays->count(), $perPage, $currentPage, [
            'path'  => LengthAwarePaginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]);

        return view('portal.attendance.index', compact('employee', 'records', 'summary'));
    }
}
