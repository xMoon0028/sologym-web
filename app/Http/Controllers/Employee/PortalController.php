<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\LeaveService;
use App\Services\TimeCreditService;

class PortalController extends Controller
{
    public function __construct(private TimeCreditService $timeCreditService) {}

    public function index()
    {
        $user = auth()->user();
        $employee = $user->employee;

        // Auto-heal: if employee is not linked by user_id yet, link by email
        if (!$employee && $user->email) {
            $employee = \App\Models\Employee::where('email', $user->email)->first();
            if ($employee) {
                $employee->update(['user_id' => $user->id]);
                $user->load('employee');
            }
        }

        if (!$employee) {
            return view('portal.no-profile');
        }

        try {
            if (!\Illuminate\Support\Facades\Cache::has('auto_expire_leaves_last_run')) {
                \Illuminate\Support\Facades\Cache::put('auto_expire_leaves_last_run', true, now()->addMinutes(15));
                app(LeaveService::class)->checkAndExpireOverdueRequests();
            }
        } catch (\Throwable $e) {
            // Non-blocking
        }

        $balance = 0;
        try {
            $balance = $this->timeCreditService->getBalance($employee);
        } catch (\Throwable $e) {}

        $recentLeaves = collect();
        try {
            $recentLeaves = $employee->leaveRequests()->latest()->take(5)->get();
        } catch (\Throwable $e) {}

        $recentTimeCredits = collect();
        try {
            $recentTimeCredits = $employee->timeCreditLedger()->orderBy('date', 'desc')->orderBy('id', 'desc')->take(5)->get();
        } catch (\Throwable $e) {}

        $recentAttendance = collect();
        try {
            $recentAttendance = $employee->attendanceRecords()->orderBy('date', 'desc')->take(7)->get();
        } catch (\Throwable $e) {}

        $recentPayslips = collect();
        try {
            $recentPayslips = $employee->payslips()->with('period')
                ->whereHas('period', fn($q) => $q->visibleToEmployees())
                ->latest()->take(3)->get();
        } catch (\Throwable $e) {}

        return view('portal.dashboard', compact(
            'employee', 'balance', 'recentLeaves', 'recentTimeCredits', 'recentAttendance', 'recentPayslips'
        ));
    }

    public function profile()
    {
        $user = auth()->user();
        $employee = $user->employee;
        if (!$employee && $user->email) {
            $employee = \App\Models\Employee::where('email', $user->email)->first();
            if ($employee) {
                $employee->update(['user_id' => $user->id]);
                $user->load('employee');
            }
        }
        if (!$employee) return view('portal.no-profile');

        $employee->load(['department', 'address', 'governmentIds', 'compensation',
            'education', 'iq', 'lastEmployment', 'family', 'certificates']);

        return view('portal.profile', compact('employee'));
    }
}
