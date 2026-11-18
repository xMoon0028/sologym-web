<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Payslip;
use App\Services\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmployeePayslipController extends Controller
{
    public function __construct(private PayrollService $payrollService) {}

    public function index()
    {
        $employee = auth()->user()->employee;
        if (!$employee) return view('portal.no-profile');

        $payslips = Payslip::where('employee_id', $employee->id)
            ->with('period')
            ->whereHas('period', fn($q) => $q->visibleToEmployees())
            ->latest()
            ->paginate(12);

        // Upcoming scheduled payslip (if any)
        $upcomingPayslip = Payslip::where('employee_id', $employee->id)
            ->with('period')
            ->whereHas('period', fn($q) => $q->where('status', 'released')->where('released_at', '>', now()))
            ->first();

        return view('portal.payslips.index', compact('employee', 'payslips', 'upcomingPayslip'));
    }

    public function pdf(Request $request, Payslip $payslip)
    {
        $employee = auth()->user()->employee;
        abort_if($payslip->employee_id !== $employee?->id, 403);
        abort_if(!$payslip->period->isVisibleToEmployees(), 403, 'This payslip is scheduled and not yet available for viewing.');

        $payslip->load(['employee.department', 'employee.compensation', 'customItems', 'period']);
        if ($payslip->period->is_editable) {
            $payslip->syncRatesAndDeductions();
        }
        $period = $payslip->period;
        $amountInWords = $this->payrollService->amountInWords((float) $payslip->net_pay);

        $pdf = Pdf::loadView('pdf.payslip', compact('payslip', 'period', 'amountInWords'))
            ->setPaper('a5', 'portrait');

        $periodSlug = Str::slug($period->name);
        $filename = "my-payslip-{$periodSlug}.pdf";

        if ($request->query('action') === 'download' || $request->routeIs('portal.payslips.download')) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}
