<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PayrollPeriod;
use App\Services\LeaveService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            if (!\Illuminate\Support\Facades\Cache::has('auto_expire_leaves_last_run')) {
                \Illuminate\Support\Facades\Cache::put('auto_expire_leaves_last_run', true, now()->addMinutes(15));
                app(LeaveService::class)->checkAndExpireOverdueRequests();
            }
        } catch (\Throwable $e) {
            // Non-blocking
        }

        $totalEmployees = Employee::where('is_active', true)->count();
        $newHiresThisMonth = Employee::where('is_active', true)
            ->whereMonth('date_hired', now()->month)
            ->whereYear('date_hired', now()->year)
            ->count();

        $pendingLeavesCount = LeaveRequest::whereIn('status', [
            'pending_section_head',
            'pending_hr',
            'pending_executive',
            'pending_executive_director',
        ])->count();

        $recentLeaves = LeaveRequest::with(['employee.department'])
            ->latest()
            ->take(5)
            ->get();

        $recentPayroll = PayrollPeriod::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalEmployees', 'newHiresThisMonth', 'pendingLeavesCount', 'recentLeaves', 'recentPayroll'
        ));
    }

    /**
     * Test live email connectivity.
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $result = app(\App\Services\NotificationService::class)->testEmailConnection($request->email);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }
}
