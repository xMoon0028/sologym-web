<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Services\LoanService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LoanController extends Controller
{
    public function __construct(
        protected LoanService $loanService
    ) {}

    /**
     * Display a listing of all employee loans with KPI cards and filters.
     */
    public function index(Request $request)
    {
        $query = EmployeeLoan::with(['employee.department', 'creator'])->latest();

        // Filters
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('loan_type', $request->type);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('loan_name', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('name', 'like', "%{$search}%")
                         ->orWhere('employee_no', 'like', "%{$search}%");
                  });
            });
        }

        $loans = $query->paginate(20)->withQueryString();

        // Summary KPI Metrics
        $totalActiveCount = EmployeeLoan::where('status', 'active')->count();
        $totalOutstanding = (float) EmployeeLoan::where('status', 'active')->sum('remaining_balance');
        $totalCutoffDeductions = (float) EmployeeLoan::where('status', 'active')->sum('amortization_per_cutoff');
        $totalCollected = (float) EmployeeLoan::sum('amount_paid');

        // Type Breakdown Counts
        $typeCounts = [
            'all'       => EmployeeLoan::count(),
            'sss'       => EmployeeLoan::where('loan_type', 'sss')->count(),
            'pagibig'   => EmployeeLoan::where('loan_type', 'pagibig')->count(),
            'salary'    => EmployeeLoan::where('loan_type', 'salary')->count(),
            'emergency' => EmployeeLoan::where('loan_type', 'emergency')->count(),
            'other'     => EmployeeLoan::where('loan_type', 'other')->count(),
        ];

        $statusCounts = [
            'active'    => $totalActiveCount,
            'completed' => EmployeeLoan::where('status', 'completed')->count(),
            'paused'    => EmployeeLoan::where('status', 'paused')->count(),
            'cancelled' => EmployeeLoan::where('status', 'cancelled')->count(),
        ];

        $departments = Department::orderBy('name')->get();
        $employees = Employee::where('is_active', true)->orderBy('name')->get(['id', 'name', 'employee_no', 'department_id']);

        return view('admin.loans.index', compact(
            'loans',
            'totalActiveCount',
            'totalOutstanding',
            'totalCutoffDeductions',
            'totalCollected',
            'typeCounts',
            'statusCounts',
            'departments',
            'employees'
        ));
    }

    /**
     * Show detailed loan ledger and payment history.
     */
    public function show(EmployeeLoan $loan)
    {
        $loan->load(['employee.department', 'payments.payslip.period', 'payments.recorder', 'creator']);

        return view('admin.loans.show', compact('loan'));
    }

    /**
     * Store a newly created loan in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id'            => 'required|exists:employees,id',
            'loan_type'              => 'required|string|in:sss,pagibig,salary,emergency,other',
            'loan_name'              => 'required|string|max:255',
            'reference_no'           => 'nullable|string|max:100',
            'principal_amount'       => 'required|numeric|min:0.01',
            'interest_amount'        => 'nullable|numeric|min:0',
            'total_amount'           => 'nullable|numeric|min:0.01',
            'monthly_amortization'   => 'nullable|numeric|min:0',
            'amortization_per_cutoff'=> 'required|numeric|min:0.01',
            'deduction_schedule'     => 'required|in:both,mid_month,end_month',
            'start_date'             => 'required|date',
            'end_date'               => 'nullable|date|after_or_equal:start_date',
            'status'                 => 'nullable|in:active,paused,completed,cancelled',
            'notes'                  => 'nullable|string|max:1000',
        ]);

        $loan = $this->loanService->createLoan($validated, auth()->id());

        return redirect()->route('admin.loans.show', $loan)
            ->with('success', "Loan {$loan->loan_name} recorded successfully for {$loan->employee->name}.");
    }

    /**
     * Update the specified loan in storage.
     */
    public function update(Request $request, EmployeeLoan $loan)
    {
        $validated = $request->validate([
            'loan_type'              => 'required|string|in:sss,pagibig,salary,emergency,other',
            'loan_name'              => 'required|string|max:255',
            'reference_no'           => 'nullable|string|max:100',
            'principal_amount'       => 'required|numeric|min:0.01',
            'interest_amount'        => 'nullable|numeric|min:0',
            'total_amount'           => 'nullable|numeric|min:0.01',
            'monthly_amortization'   => 'nullable|numeric|min:0',
            'amortization_per_cutoff'=> 'required|numeric|min:0.01',
            'deduction_schedule'     => 'required|in:both,mid_month,end_month',
            'start_date'             => 'required|date',
            'end_date'               => 'nullable|date|after_or_equal:start_date',
            'status'                 => 'required|in:active,paused,completed,cancelled',
            'notes'                  => 'nullable|string|max:1000',
        ]);

        $this->loanService->updateLoan($loan, $validated);

        return back()->with('success', "Loan {$loan->loan_name} updated successfully.");
    }

    /**
     * Record a manual payment or direct cash adjustment.
     */
    public function recordPayment(Request $request, EmployeeLoan $loan)
    {
        $validated = $request->validate([
            'amount'       => 'required|numeric|min:0.01',
            'payment_type' => 'required|in:manual_payment,adjustment',
            'reference_no' => 'nullable|string|max:100',
            'remarks'      => 'nullable|string|max:500',
        ]);

        $payment = $this->loanService->recordManualPayment(
            $loan,
            (float) $validated['amount'],
            $validated['payment_type'],
            $validated['reference_no'] ?? null,
            $validated['remarks'] ?? null,
            auth()->id()
        );

        $formattedAmt = number_format($payment->amount, 2);
        return back()->with('success', "Manual payment of ₱{$formattedAmt} recorded. Remaining balance is ₱" . number_format($loan->remaining_balance, 2) . ".");
    }

    /**
     * Delete loan if no payments recorded, or prevent data loss.
     */
    public function destroy(EmployeeLoan $loan)
    {
        if ($loan->payments()->count() > 0) {
            return back()->with('error', 'Cannot delete a loan with recorded payments. You may set its status to "Cancelled" instead.');
        }

        $loan->delete();
        return redirect()->route('admin.loans.index')->with('success', 'Loan record deleted successfully.');
    }

    /**
     * Export loans to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $loans = EmployeeLoan::with(['employee.department'])->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="employee_loans_' . date('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($loans) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Loan ID',
                'Employee No',
                'Employee Name',
                'Department',
                'Loan Type',
                'Loan Name',
                'Reference No',
                'Principal (PHP)',
                'Interest (PHP)',
                'Total Obligation (PHP)',
                'Amortization / Cutoff (PHP)',
                'Deduction Schedule',
                'Amount Paid (PHP)',
                'Remaining Balance (PHP)',
                'Start Date',
                'End Date',
                'Status',
            ]);

            foreach ($loans as $loan) {
                fputcsv($handle, [
                    $loan->id,
                    $loan->employee?->employee_no ?? '',
                    $loan->employee?->name ?? '',
                    $loan->employee?->department?->name ?? '',
                    $loan->loan_type,
                    $loan->loan_name,
                    $loan->reference_no ?? '',
                    $loan->principal_amount,
                    $loan->interest_amount,
                    $loan->total_amount,
                    $loan->amortization_per_cutoff,
                    $loan->deduction_schedule,
                    $loan->amount_paid,
                    $loan->remaining_balance,
                    $loan->start_date?->toDateString() ?? '',
                    $loan->end_date?->toDateString() ?? '',
                    $loan->status,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
