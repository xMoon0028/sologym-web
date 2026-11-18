<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\HealthAssistanceFund;
use Illuminate\Http\Request;

class EmployeeHafController extends Controller
{
    public function index(Request $request)
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return redirect()->route('portal.dashboard')
                ->with('error', 'Employee record not found for your account.');
        }

        $today = now('Asia/Manila')->startOfDay();

        // 1. Current active or expiring soon fund
        $activeFund = HealthAssistanceFund::with(['claims.processor'])
            ->where('employee_id', $employee->id)
            ->where('status', '!=', 'cancelled')
            ->where('end_date', '>=', $today->toDateString())
            ->orderBy('start_date', 'desc')
            ->first();

        // If no currently active fund, retrieve the most recent one (even if expired)
        $currentOrLatestFund = $activeFund ?: HealthAssistanceFund::with(['claims.processor'])
            ->where('employee_id', $employee->id)
            ->orderBy('start_date', 'desc')
            ->first();

        // 2. Historical membership cycles
        $historyFunds = HealthAssistanceFund::where('employee_id', $employee->id)
            ->when($currentOrLatestFund, fn($q) => $q->where('id', '!=', $currentOrLatestFund->id))
            ->orderBy('start_date', 'desc')
            ->get();

        return view('portal.haf.index', [
            'employee'            => $employee,
            'currentFund'         => $currentOrLatestFund,
            'historyFunds'        => $historyFunds,
            'isActiveFund'        => $activeFund !== null,
        ]);
    }
}
