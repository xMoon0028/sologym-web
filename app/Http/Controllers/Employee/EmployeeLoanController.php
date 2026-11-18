<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeLoan;
use Illuminate\Http\Request;

class EmployeeLoanController extends Controller
{
    /**
     * Display the employee's loans and repayment progress.
     */
    public function index(Request $request)
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return redirect()->route('portal.dashboard')->with('error', 'No employee profile linked to your account.');
        }

        $activeLoans = EmployeeLoan::where('employee_id', $employee->id)
            ->where('status', 'active')
            ->with(['payments.payslip.period'])
            ->latest('start_date')
            ->get();

        $completedLoans = EmployeeLoan::where('employee_id', $employee->id)
            ->whereIn('status', ['completed', 'paused', 'cancelled'])
            ->with(['payments.payslip.period'])
            ->latest('start_date')
            ->get();

        // Recent Payments across all employee loans
        $allLoanIds = $employee->loans()->pluck('id');
        $recentPayments = \App\Models\LoanPayment::whereIn('employee_loan_id', $allLoanIds)
            ->with(['loan', 'payslip.period'])
            ->latest('payment_date')
            ->paginate(15);

        // Summary metrics
        $totalBalance = (float) $activeLoans->sum('remaining_balance');
        $totalCutoffDeduction = (float) $activeLoans->sum('amortization_per_cutoff');
        $totalRepaid = (float) $employee->loans()->sum('amount_paid');
        $activeCount = $activeLoans->count();

        return view('portal.loans.index', compact(
            'employee',
            'activeLoans',
            'completedLoans',
            'recentPayments',
            'totalBalance',
            'totalCutoffDeduction',
            'totalRepaid',
            'activeCount'
        ));
    }

    /**
     * Display a specific loan with full statement and ledger.
     */
    public function show(EmployeeLoan $loan)
    {
        $employee = auth()->user()->employee;

        if (!$employee || $loan->employee_id !== $employee->id) {
            abort(403, 'Unauthorized access to loan statement.');
        }

        $loan->load(['payments.payslip.period', 'payments.recorder']);

        return view('portal.loans.show', compact('loan', 'employee'));
    }
}
