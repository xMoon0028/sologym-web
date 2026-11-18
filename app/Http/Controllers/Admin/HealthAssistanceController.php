<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\HealthAssistanceFund;
use App\Services\HealthAssistanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HealthAssistanceController extends Controller
{
    public function __construct(
        protected HealthAssistanceService $hafService
    ) {}

    public function index(Request $request)
    {
        $today = now('Asia/Manila')->startOfDay();
        $thirtyDaysOut = $today->copy()->addDays(30)->endOfDay();

        // High-level statistics (optimized into a single aggregated query)
        $todayStr = $today->toDateString();
        $thirtyDaysOutStr = $thirtyDaysOut->toDateString();

        $stats = HealthAssistanceFund::selectRaw("
            COUNT(CASE WHEN status != 'cancelled' AND end_date >= ? THEN 1 END) as total_active,
            COUNT(CASE WHEN status != 'cancelled' AND end_date >= ? AND end_date <= ? THEN 1 END) as expiring_soon,
            COUNT(CASE WHEN status != 'cancelled' AND end_date < ? THEN 1 END) as total_expired,
            COALESCE(SUM(CASE WHEN status != 'cancelled' THEN contribution_amount ELSE 0 END), 0) as total_contributions,
            COALESCE(SUM(CASE WHEN status != 'cancelled' THEN amount_used ELSE 0 END), 0) as total_claims,
            COALESCE(SUM(CASE WHEN status != 'cancelled' AND end_date >= ? THEN remaining_balance ELSE 0 END), 0) as total_remaining
        ", [$todayStr, $todayStr, $thirtyDaysOutStr, $todayStr, $todayStr])->first();

        $totalActive = (int) ($stats->total_active ?? 0);
        $expiringSoonCount = (int) ($stats->expiring_soon ?? 0);
        $expiredCount = (int) ($stats->total_expired ?? 0);
        $totalContributions = (float) ($stats->total_contributions ?? 0);
        $totalClaimsDisbursed = (float) ($stats->total_claims ?? 0);
        $totalRemainingCoverage = (float) ($stats->total_remaining ?? 0);

        // Query list
        $query = HealthAssistanceFund::with(['employee.department', 'claims'])
            ->orderBy('start_date', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('member_number', 'like', "%{$search}%")
                  ->orWhere('receipt_no', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('name', 'like', "%{$search}%")
                         ->orWhere('employee_no', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', fn($eq) => $eq->where('department_id', $request->department_id));
        }

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'active') {
                $query->where('status', '!=', 'cancelled')
                      ->where('end_date', '>', $thirtyDaysOut->toDateString());
            } elseif ($status === 'expiring_soon') {
                $query->where('status', '!=', 'cancelled')
                      ->where('end_date', '>=', $today->toDateString())
                      ->where('end_date', '<=', $thirtyDaysOut->toDateString());
            } elseif ($status === 'expired') {
                $query->where('status', '!=', 'cancelled')
                      ->where('end_date', '<', $today->toDateString());
            } elseif ($status === 'cancelled') {
                $query->where('status', 'cancelled');
            }
        }

        $funds = $query->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->get();
        $employees = Employee::active()
            ->select('id', 'name', 'employee_no', 'department_id')
            ->with('department:id,name')
            ->orderBy('name')
            ->get();

        return view('admin.haf.index', compact(
            'funds',
            'departments',
            'employees',
            'totalActive',
            'expiringSoonCount',
            'expiredCount',
            'totalContributions',
            'totalClaimsDisbursed',
            'totalRemainingCoverage'
        ));
    }

    public function create()
    {
        $employees = Employee::active()->orderBy('name')->get();
        return view('admin.haf.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'         => 'required|exists:employees,id',
            'start_date'          => 'required|date',
            'validity_days'       => 'nullable|integer|min:1|max:730',
            'end_date'            => 'nullable|date|after_or_equal:start_date',
            'contribution_amount' => 'required|numeric|min:0',
            'coverage_amount'     => 'nullable|numeric|min:0',
            'payment_method'      => 'nullable|string|max:50',
            'receipt_no'          => 'nullable|string|max:100',
            'receipt_file'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'notes'               => 'nullable|string|max:1000',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $fund = $this->hafService->enroll($employee, $request->all(), auth()->user());

        return redirect()->route('admin.haf.show', $fund)
            ->with('success', "Health Assistance Fund (HMO) membership enrolled successfully for {$employee->name}.");
    }

    public function show(HealthAssistanceFund $haf)
    {
        $haf->load(['employee.department', 'claims.processor', 'creator']);
        return view('admin.haf.show', compact('haf'));
    }

    public function edit(HealthAssistanceFund $haf)
    {
        return view('admin.haf.edit', compact('haf'));
    }

    public function update(Request $request, HealthAssistanceFund $haf)
    {
        $request->validate([
            'member_number'       => 'nullable|string|max:50',
            'plan_year'           => 'nullable|string|max:30',
            'start_date'          => 'required|date',
            'end_date'            => 'required|date|after_or_equal:start_date',
            'contribution_amount' => 'required|numeric|min:0',
            'coverage_amount'     => 'required|numeric|min:0',
            'payment_method'      => 'nullable|string|max:50',
            'receipt_no'          => 'nullable|string|max:100',
            'receipt_file'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'status'              => 'required|in:active,cancelled',
            'notes'               => 'nullable|string|max:1000',
        ]);

        $data = $request->only([
            'member_number',
            'plan_year',
            'start_date',
            'end_date',
            'contribution_amount',
            'coverage_amount',
            'payment_method',
            'receipt_no',
            'status',
            'notes',
        ]);

        if ($request->hasFile('receipt_file')) {
            if ($haf->receipt_file_path) {
                Storage::disk('public')->delete($haf->receipt_file_path);
            }
            $data['receipt_file_path'] = $request->file('receipt_file')->store('haf-receipts', 'public');
        }

        $haf->update($data);
        $haf->recomputeBalance();

        return redirect()->route('admin.haf.show', $haf)
            ->with('success', 'Health Assistance Fund details updated successfully.');
    }

    public function destroy(HealthAssistanceFund $haf)
    {
        $name = $haf->employee->name ?? 'Member';
        if ($haf->receipt_file_path) {
            Storage::disk('public')->delete($haf->receipt_file_path);
        }
        $haf->delete();

        return redirect()->route('admin.haf.index')
            ->with('success', "Health Assistance Fund record for {$name} has been deleted.");
    }

    public function storeClaim(Request $request, HealthAssistanceFund $haf)
    {
        $request->validate([
            'claim_date'          => 'required|date',
            'claim_type'          => 'required|string|max:50',
            'healthcare_provider' => 'nullable|string|max:255',
            'diagnosis_reason'    => 'required|string|max:1000',
            'amount_requested'    => 'required|numeric|min:1',
            'amount_approved'     => 'required|numeric|min:0',
            'receipt_file'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'remarks'             => 'nullable|string|max:1000',
        ]);

        $claim = $this->hafService->recordClaim($haf, $request->all(), auth()->user());

        return redirect()->route('admin.haf.show', $haf)
            ->with('success', "Medical assistance claim of ₱" . number_format($claim->amount_approved, 2) . " successfully recorded.");
    }

    public function renew(Request $request, HealthAssistanceFund $haf)
    {
        $request->validate([
            'start_date'          => 'required|date',
            'validity_days'       => 'nullable|integer|min:1|max:730',
            'end_date'            => 'nullable|date|after_or_equal:start_date',
            'contribution_amount' => 'required|numeric|min:0',
            'coverage_amount'     => 'nullable|numeric|min:0',
            'payment_method'      => 'nullable|string|max:50',
            'receipt_no'          => 'nullable|string|max:100',
            'receipt_file'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'notes'               => 'nullable|string|max:1000',
        ]);

        $newFund = $this->hafService->renew($haf, $request->all(), auth()->user());

        return redirect()->route('admin.haf.show', $newFund)
            ->with('success', "HAF coverage successfully renewed for 365 days until " . $newFund->end_date->format('M d, Y') . ".");
    }

    public function sendNotice(HealthAssistanceFund $haf)
    {
        $success = $this->hafService->sendManualExpirationNotice($haf);
        if ($success) {
            return back()->with('success', "30-day expiration notice sent to {$haf->employee->name}.");
        }
        return back()->with('error', "Could not send expiration notice to {$haf->employee->name}.");
    }

    public function runExpirationCheck()
    {
        $results = $this->hafService->checkAndDispatchExpirationNotices();
        return back()->with('success', "Expiration scan complete. Scanned {$results['total_expiring']} expiring memberships; sent {$results['dispatched']} notices.");
    }
}
