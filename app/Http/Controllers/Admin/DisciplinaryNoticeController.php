<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DisciplinaryNotice;
use App\Models\Employee;
use App\Services\DisciplinaryService;
use Illuminate\Http\Request;

class DisciplinaryNoticeController extends Controller
{
    public function __construct(
        protected DisciplinaryService $disciplinaryService
    ) {}

    public function index(Request $request)
    {
        $query = DisciplinaryNotice::with(['employee.department', 'issuer']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('notice_no', 'like', "%{$search}%")
                  ->orWhere('offense_title', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('name', 'like', "%{$search}%")
                         ->orWhere('employee_no', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('category_code')) {
            $query->where('category_code', $request->category_code);
        }

        if ($request->filled('sanction_type')) {
            $query->where('sanction_type', $request->sanction_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $notices = $query->latest('id')->paginate(15)->withQueryString();

        $todayStr = now('Asia/Manila')->startOfDay()->toDateString();
        $totalCount = DisciplinaryNotice::count();
        $pendingBase = DisciplinaryNotice::where('requires_explanation', true)
            ->whereNull('scanned_nte_path')
            ->whereNull('nte_received_at')
            ->whereNotIn('status', ['resolved', 'closed']);

        $overdueCount = (clone $pendingBase)
            ->where('explanation_deadline', '<', $todayStr)
            ->count();

        $pendingExplanationCount = (clone $pendingBase)
            ->where('explanation_deadline', '>=', $todayStr)
            ->count();

        $resolvedCount = DisciplinaryNotice::whereIn('status', ['resolved', 'closed'])->count();

        $employees = Employee::where('is_active', true)->with('department')->orderBy('name')->get();
        $catalog = DisciplinaryNotice::getCatalog();

        return view('admin.disciplinary.index', compact(
            'notices',
            'totalCount',
            'pendingExplanationCount',
            'overdueCount',
            'resolvedCount',
            'employees',
            'catalog'
        ));
    }

    public function create()
    {
        $employees = Employee::where('is_active', true)->with('department')->orderBy('name')->get();
        $catalog = DisciplinaryNotice::getCatalog();

        return view('admin.disciplinary.create', compact('employees', 'catalog'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'category_code' => 'required|string',
            'category_name' => 'required|string',
            'classification' => 'required|in:minor,major,special',
            'offense_title' => 'nullable|string|max:1000',
            'custom_offense_title' => 'nullable|string|max:1000',
            'offense_occurrence' => 'required|string|max:32',
            'sanction_type' => 'required|string|max:64',
            'requires_explanation' => 'nullable|boolean',
            'explanation_deadline' => 'nullable|date',
            'incident_date' => 'required|date',
            'incident_time' => 'nullable|string|max:32',
            'incident_location' => 'nullable|string|max:255',
            'description' => 'required|string|min:5|max:5000',
            'hr_clarification_note' => 'nullable|string|max:500',
            'incident_report' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'documentary_evidence' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        if (empty($validated['offense_title']) && empty($validated['custom_offense_title'])) {
            return back()->withErrors(['offense_title' => 'Please select an offense or enter a description for the offense.'])->withInput();
        }

        $notice = $this->disciplinaryService->createNotice(
            $validated,
            auth()->user(),
            $request->file('incident_report'),
            $request->file('documentary_evidence')
        );

        return redirect()->route('admin.disciplinary.show', $notice)
            ->with('success', "Disciplinary notice {$notice->notice_no} successfully issued and notified to {$notice->employee->name}.");
    }

    public function show(DisciplinaryNotice $disciplinary)
    {
        $disciplinary->load(['employee.department', 'issuer', 'resolver', 'nteProcessor']);
        return view('admin.disciplinary.show', ['notice' => $disciplinary]);
    }

    public function attachNte(Request $request, DisciplinaryNotice $disciplinary)
    {
        $request->validate([
            'scanned_nte' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $this->disciplinaryService->attachScannedNte(
            $disciplinary,
            $request->file('scanned_nte'),
            $request->remarks,
            auth()->user()
        );

        return back()->with('success', "Scanned copy of physical NTE successfully attached. Time-in restriction has been cleared for {$disciplinary->employee->name}.");
    }

    public function resolve(Request $request, DisciplinaryNotice $disciplinary)
    {
        $validated = $request->validate([
            'hr_resolution_notes' => 'required|string|min:5|max:3000',
            'final_sanction' => 'nullable|string|max:255',
            'status' => 'required|in:resolved,closed,under_review',
        ]);

        $this->disciplinaryService->resolveNotice($disciplinary, $validated, auth()->user());

        return redirect()->route('admin.disciplinary.show', $disciplinary)
            ->with('success', "Disciplinary notice {$disciplinary->notice_no} resolution recorded successfully.");
    }
}
