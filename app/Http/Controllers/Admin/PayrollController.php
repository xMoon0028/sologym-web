<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PayrollPeriodExport;
use App\Http\Controllers\Controller;
use App\Imports\AttendanceImport;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\PayrollPeriod;
use App\Models\PayslipCustomItem;
use App\Services\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

use App\Services\SpreadsheetImportService;

class PayrollController extends Controller
{
    public function __construct(
        private PayrollService $payrollService,
        private SpreadsheetImportService $importService
    ) {}

    public function index()
    {
        try {
            app(\App\Services\NotificationService::class)->checkAndDispatchScheduledReleases();
        } catch (\Throwable $e) {
            // Non-blocking
        }

        $periods = PayrollPeriod::withCount('payslips')->latest()->paginate(15);
        return view('admin.payroll.index', compact('periods'));
    }

    public function create()
    {
        return view('admin.payroll.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'date_of_payment' => 'nullable|date',
            'cutoff_type' => 'nullable|in:auto,mid_month,end_month',
            'released_at' => 'nullable|date',
        ]);

        $cutoffType = $request->cutoff_type;
        if (!$cutoffType || $cutoffType === 'auto') {
            $cutoffType = PayrollPeriod::detectCutoffType($request->start_date, $request->end_date);
        }

        $period = PayrollPeriod::create([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'date_of_payment' => $request->date_of_payment,
            'cutoff_type' => $cutoffType,
            'released_at' => $request->released_at ? \Carbon\Carbon::parse($request->released_at) : null,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.payroll.show', $period)->with('success', 'Payroll period created. Now upload attendance data.');
    }

    /**
     * Toggle cutoff type between mid_month (PhilHealth+HDMF) and end_month (SSS) and re-sync drafts.
     */
    public function toggleCutoffType(PayrollPeriod $period)
    {
        if (!$period->is_editable) {
            return back()->with('error', 'This payroll period is already finalized.');
        }

        $newType = $period->is_mid_month ? 'end_month' : 'mid_month';
        $period->update(['cutoff_type' => $newType]);

        // Re-sync all payslips to update deductions under the new cutoff type
        $employees = Employee::with('compensation')->where('is_active', true)->get();
        foreach ($employees as $employee) {
            $this->payrollService->generateDraft($employee, $period);
        }

        $label = $newType === 'mid_month' ? 'Mid-Month Salary (PhilHealth & HDMF)' : 'End-Month Salary (SSS)';
        return redirect()->route('admin.payroll.show', $period)
            ->with('success', "Cutoff switched to {$label}. All employee statutory deductions have been updated.");
    }

    public function show(PayrollPeriod $period)
    {
        if ($period->is_editable) {
            $this->payrollService->refreshRatesAndDeductionsForPeriod($period);
        }
        $payslips = $this->payrollService->getSortedPayslipsForPeriod($period);
        return view('admin.payroll.show', compact('period', 'payslips'));
    }

    /**
     * Step 2: Upload attendance CSV/Excel, auto-populate payslip drafts.
     */
    public function uploadAttendance(Request $request, PayrollPeriod $period)
    {
        if (!$period->is_editable) {
            return back()->with('error', 'This payroll period is already finalized.');
        }

        $file = $this->importService->validateSpreadsheet($request);

        // Import attendance records for this period
        $import = $this->importService->importAttendance($file);

        // Generate payslip drafts for all active employees
        $employees = Employee::with('compensation')->where('is_active', true)->get();
        $generated = 0;
        foreach ($employees as $employee) {
            $this->payrollService->generateDraft($employee, $period);
            $generated++;
        }

        $period->update(['status' => 'under_review']);

        $empCount = count($import->employeesProcessed);
        return redirect()->route('admin.payroll.show', $period)
            ->with('success', "Attendance imported ({$import->imported} records across {$empCount} employees). {$generated} payslip drafts generated. Review and edit below.");
    }

    /**
     * Step 2b: Re-sync attendance records and employee compensation profiles for this period.
     */
    public function syncAttendance(PayrollPeriod $period)
    {
        if (!$period->is_editable) {
            return back()->with('error', 'This payroll period is already finalized.');
        }

        $employees = Employee::with('compensation')->where('is_active', true)->get();
        $synced = 0;
        foreach ($employees as $employee) {
            $this->payrollService->generateDraft($employee, $period);
            $synced++;
        }

        if ($period->status === 'draft') {
            $period->update(['status' => 'under_review']);
        }

        return redirect()->route('admin.payroll.show', $period)
            ->with('success', "Attendance logs and profile compensation synced for {$synced} employees.");
    }

    /**
     * Step 3: Inline update a single payslip row.
     */
    public function updatePayslip(Request $request, PayrollPeriod $period, Payslip $payslip)
    {
        if (!$period->is_editable) {
            return response()->json(['error' => 'Period is finalized'], 403);
        }

        $payslip->update($request->only([
            'salary_and_allowances', 'holiday_pay', 'other_earnings',
            'sss_deduction', 'ph_deduction', 'hdmf_deduction',
            'absent_days', 'absences_deduction',
            'late_minutes', 'late_deduction',
            'undertime_minutes', 'undertime_deduction',
            'loan_deduction', 'sss_loan_deduction', 'pagibig_loan_deduction', 'salary_loan_deduction', 'other_deductions',
        ]));

        $payslip->recompute();

        return response()->json([
            'gross_earnings' => number_format($payslip->fresh()->gross_earnings, 2),
            'total_deductions' => number_format($payslip->fresh()->total_deductions, 2),
            'net_pay' => number_format($payslip->fresh()->net_pay, 2),
        ]);
    }

    /**
     * Step 3b: Add a custom earning/deduction line item.
     */
    public function addItem(Request $request, PayrollPeriod $period, Payslip $payslip)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|in:earning,deduction',
            'amount' => 'required|numeric|min:0',
        ]);

        $payslip->customItems()->create($request->only(['label', 'type', 'amount']));
        $payslip->recompute();

        return back()->with('success', 'Item added.');
    }

    public function removeItem(Request $request, PayrollPeriod $period, Payslip $payslip, PayslipCustomItem $item)
    {
        $item->delete();
        $payslip->recompute();
        return back()->with('success', 'Item removed.');
    }

    /**
     * Recompute a single payslip.
     */
    public function recompute(PayrollPeriod $period, Payslip $payslip)
    {
        $payslip->load(['employee.compensation']);
        $payslip->syncRatesAndDeductions();
        return back()->with('success', 'Payslip recomputed.');
    }

    /**
     * Step 4a: Finalize — lock all payslips.
     */
    public function finalize(PayrollPeriod $period)
    {
        if ($period->status === 'released') {
            return back()->with('error', 'Period already released.');
        }

        $period->payslips()->update(['is_finalized' => true]);
        $period->update([
            'status' => 'finalized',
            'finalized_by' => auth()->id(),
            'finalized_at' => now(),
        ]);

        // Post loan payments for active employee loans deducted in this period
        app(\App\Services\LoanService::class)->applyPayrollDeductionsForPeriod($period);

        return redirect()->route('admin.payroll.show', $period)->with('success', 'Payroll finalized. Ready to release.');
    }

    /**
     * Step 4b: Release or Schedule Release — employees can view payslips now or at a scheduled time.
     */
    public function release(Request $request, PayrollPeriod $period)
    {
        $validated = $request->validate([
            'release_type' => 'nullable|in:now,scheduled',
            'released_at' => 'nullable|date',
        ]);

        // Ensure loan deductions are posted if not already done during finalization
        app(\App\Services\LoanService::class)->applyPayrollDeductionsForPeriod($period);

        $sentCount = 0;
        if (($validated['release_type'] ?? 'now') === 'scheduled' && !empty($validated['released_at'])) {
            $releaseTime = \Carbon\Carbon::parse($validated['released_at']);
            $period->update([
                'status' => 'released',
                'released_at' => $releaseTime,
            ]);

            if ($releaseTime->isFuture()) {
                return redirect()->route('admin.payroll.show', $period)
                    ->with('success', 'Payslip release scheduled for ' . $releaseTime->format('M d, Y \a\t h:i A') . '. Staff will not see their payslips until this time.');
            }
            $sentCount = app(\App\Services\NotificationService::class)->notifyPayrollPeriodReleased($period);
        } else {
            $period->update([
                'status' => 'released',
                'released_at' => now(),
            ]);
            $sentCount = app(\App\Services\NotificationService::class)->notifyPayrollPeriodReleased($period);
        }

        $notice = $sentCount > 0 ? " ({$sentCount} staff notifications dispatched)" : "";
        return redirect()->route('admin.payroll.show', $period)
            ->with('success', "Payslips released! Staff can now view and download their payslips.{$notice}");
    }

    /**
     * Resend/Force send payslip notification emails for this period.
     */
    public function sendEmails(PayrollPeriod $period)
    {
        $sentCount = app(\App\Services\NotificationService::class)->notifyPayrollPeriodReleased($period, true);
        return back()->with('success', "Payslip email dispatch completed! Dispatched to {$sentCount} employee(s).");
    }

    /**
     * View or Download PDF for one payslip.
     */
    public function downloadPdf(Request $request, PayrollPeriod $period, Payslip $payslip)
    {
        $payslip->load(['employee.department', 'employee.compensation', 'customItems']);
        if ($period->is_editable) {
            $payslip->syncRatesAndDeductions();
        }
        $amountInWords = $this->payrollService->amountInWords((float) $payslip->net_pay);

        $pdf = Pdf::loadView('pdf.payslip', compact('payslip', 'period', 'amountInWords'))
            ->setPaper('a5', 'portrait');

        $empNo = $payslip->employee->employee_no ?? ('ID-' . $payslip->employee_id);
        $periodSlug = \Illuminate\Support\Str::slug($period->name);
        $filename = "payslip-{$empNo}-{$periodSlug}.pdf";

        if ($request->query('action') === 'download' || $request->routeIs('admin.payroll.download-pdf')) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    /**
     * Export all payslips for a finalized/released period to Excel or CSV.
     */
    public function exportAll(Request $request, PayrollPeriod $period)
    {
        if (!in_array($period->status, ['finalized', 'released'])) {
            return redirect()->route('admin.payroll.show', $period)
                ->with('error', 'Payroll must be finalized before it can be exported.');
        }

        $format = strtolower((string) $request->query('format', 'xlsx'));
        $ext = $format === 'csv' ? 'csv' : 'xlsx';
        $writerType = $format === 'csv'
            ? \Maatwebsite\Excel\Excel::CSV
            : \Maatwebsite\Excel\Excel::XLSX;

        $slug = \Illuminate\Support\Str::slug($period->name ?: 'payroll-period');
        $filename = "LASAC_Payroll_Register_{$slug}_" . now()->format('Y-m-d') . ".{$ext}";

        return Excel::download(new PayrollPeriodExport($period), $filename, $writerType);
    }
}
