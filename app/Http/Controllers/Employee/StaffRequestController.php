<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\StaffRequest;
use App\Services\StaffRequestService;
use Illuminate\Http\Request;

class StaffRequestController extends Controller
{
    protected StaffRequestService $service;

    public function __construct(StaffRequestService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $employee = auth()->user()->employee;
        if (!$employee) {
            abort(403, 'Employee profile not associated with this user.');
        }

        $query = StaffRequest::where('employee_id', $employee->id)->with('approvals');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $requests = $query->latest()->paginate(15)->withQueryString();

        // Check if employee has pending approval reviews for subordinates / department staff
        $pendingSectionReviews = collect();
        if ($employee->approval_role === 'section_head') {
            $pendingSectionReviews = StaffRequest::where('type', 'seminar')
                ->where('status', 'pending_section_head')
                ->whereHas('employee', fn($q) => $q->where('department_id', $employee->department_id)->where('id', '!=', $employee->id))
                ->with(['employee.department', 'approvals'])
                ->latest()
                ->get();
        }

        $pendingExecutiveReviews = collect();
        if (in_array($employee->approval_role, ['executive_office', 'executive_director'])) {
            $targetStatuses = $employee->approval_role === 'executive_director'
                ? ['pending_executive', 'pending_executive_director']
                : ['pending_executive'];

            $pendingExecutiveReviews = StaffRequest::where('type', 'seminar')
                ->whereIn('status', $targetStatuses)
                ->where('employee_id', '!=', $employee->id)
                ->with(['employee.department', 'approvals'])
                ->latest()
                ->get();
        }

        return view('portal.requests.index', compact('requests', 'employee', 'pendingSectionReviews', 'pendingExecutiveReviews'));
    }

    public function createSeminar()
    {
        $employee = auth()->user()->employee;
        return view('portal.requests.create-seminar', compact('employee'));
    }

    public function storeSeminar(Request $request)
    {
        $employee = auth()->user()->employee;
        if (!$employee) {
            abort(403, 'Employee profile not associated with this user.');
        }

        $validated = $request->validate([
            'seminar_title' => 'required|string|max:255',
            'organizer' => 'nullable|string|max:255',
            'venue' => 'nullable|string|max:255',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'estimated_budget' => 'nullable|numeric|min:0',
            'justification' => 'required|string|max:2000',
            'invitation_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        $seminarRequest = $this->service->createSeminarRequest($employee, $validated, $request->file('invitation_file'));

        $stageName = match ($seminarRequest->current_stage) {
            'section_head' => 'Section Head endorsement',
            'executive_office' => 'Executive Office final approval',
            'executive_director' => 'Executive Director final approval',
            default => 'approval',
        };

        return redirect()->route('portal.requests.show', $seminarRequest)
            ->with('success', "Your Seminar Request has been submitted and routed for {$stageName}. Email notifications have been dispatched.");
    }

    public function createDocument()
    {
        $employee = auth()->user()->employee;
        return view('portal.requests.create-document', compact('employee'));
    }

    public function storeDocument(Request $request)
    {
        $employee = auth()->user()->employee;
        if (!$employee) {
            abort(403, 'Employee profile not associated with this user.');
        }

        $validated = $request->validate([
            'document_type' => 'required|string|max:255',
            'purpose' => 'required|string|max:1000',
            'date_needed' => 'nullable|date|after_or_equal:today',
        ]);

        $docRequest = $this->service->createDocumentRequest($employee, $validated);

        return redirect()->route('portal.requests.show', $docRequest)
            ->with('success', 'Your Document Request has been submitted to HR. You will be notified via email when ready.');
    }

    public function show(StaffRequest $staffRequest)
    {
        $employee = auth()->user()->employee;
        $isSuperAdmin = (!auth()->user()->employee && auth()->user()->email === 'admin@lasac.com') || auth()->user()->hasRole('admin');

        $isOwner = $employee && $staffRequest->employee_id === $employee->id;
        $isSectionHead = $employee && $employee->approval_role === 'section_head'
            && $employee->department_id === $staffRequest->employee->department_id
            && in_array($staffRequest->employee->approval_role, ['none', 'hr_officer'])
            && $employee->id !== $staffRequest->employee_id;
        $isExecutive = $employee && in_array($employee->approval_role, ['executive_office', 'executive_director']) && $employee->id !== $staffRequest->employee_id;
        $isHrOfficer = $employee && $employee->approval_role === 'hr_officer';

        if (!$isOwner && !$isSectionHead && !$isExecutive && !$isHrOfficer && !$isSuperAdmin) {
            abort(403, 'Unauthorized access to this staff request.');
        }

        $staffRequest->load(['employee.department', 'employee.user', 'reviewer', 'reportAcknowledger', 'approvals.approver']);

        $canSectionApprove = $staffRequest->is_seminar
            && $staffRequest->status === 'pending_section_head'
            && ($isSectionHead || $isSuperAdmin);

        $canExecutiveApprove = $staffRequest->is_seminar
            && in_array($staffRequest->status, ['pending_executive', 'pending_executive_director'])
            && ($isExecutive || $isSuperAdmin);

        return view('portal.requests.show', compact(
            'staffRequest',
            'employee',
            'isOwner',
            'isSectionHead',
            'isExecutive',
            'canSectionApprove',
            'canExecutiveApprove'
        ));
    }

    public function sectionApprove(Request $request, StaffRequest $staffRequest)
    {
        $employee = auth()->user()->employee;
        $isSuperAdmin = (!auth()->user()->employee && auth()->user()->email === 'admin@lasac.com') || auth()->user()->hasRole('admin');
        $isSectionHead = $employee && $employee->approval_role === 'section_head'
            && $employee->department_id === $staffRequest->employee->department_id
            && $employee->id !== $staffRequest->employee_id;

        if (!$isSectionHead && !$isSuperAdmin) {
            abort(403, 'Only designated Section Heads can approve regular staff seminar requests.');
        }

        if ($staffRequest->status !== 'pending_section_head') {
            abort(400, 'This seminar request is not pending Section Head approval.');
        }

        $action = $request->input('action');
        if (in_array($action, ['approve', 'approved'])) {
            $action = 'approved';
        } elseif (in_array($action, ['reject', 'rejected', 'disapprove', 'disapproved'])) {
            $action = 'rejected';
        }

        $request->merge(['action' => $action]);
        $validated = $request->validate([
            'action' => 'required|in:approved,rejected',
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->service->processSeminarApproval(
            $staffRequest,
            auth()->user(),
            'section_head',
            $validated['action'],
            $validated['notes'] ?? null
        );

        $actionText = $validated['action'] === 'rejected' ? 'disapproved' : 'endorsed and approved';
        return back()->with('success', "Seminar request has been {$actionText} by Section Head.");
    }

    public function executiveApprove(Request $request, StaffRequest $staffRequest)
    {
        $employee = auth()->user()->employee;
        $isSuperAdmin = (!auth()->user()->employee && auth()->user()->email === 'admin@lasac.com') || auth()->user()->hasRole('admin');
        $isExecutive = $employee && in_array($employee->approval_role, ['executive_office', 'executive_director']) && $employee->id !== $staffRequest->employee_id;

        if (!$isExecutive && !$isSuperAdmin) {
            abort(403, 'Only the Executive Office can grant final approval for seminar requests.');
        }

        if (!in_array($staffRequest->status, ['pending_executive', 'pending_executive_director'])) {
            abort(400, 'This seminar request is not pending Executive Office approval.');
        }

        $action = $request->input('action');
        if (in_array($action, ['approve', 'approved'])) {
            $action = 'approved';
        } elseif (in_array($action, ['reject', 'rejected', 'disapprove', 'disapproved'])) {
            $action = 'rejected';
        }

        $request->merge(['action' => $action]);
        $validated = $request->validate([
            'action' => 'required|in:approved,rejected',
            'notes' => 'nullable|string|max:1000',
        ]);

        $targetStage = $staffRequest->status === 'pending_executive_director' ? 'executive_director' : 'executive_office';

        $this->service->processSeminarApproval(
            $staffRequest,
            auth()->user(),
            $targetStage,
            $validated['action'],
            $validated['notes'] ?? null
        );

        $actionText = $validated['action'] === 'rejected' ? 'disapproved' : 'officially approved';
        return back()->with('success', "Seminar request has been {$actionText} by the Executive Office.");
    }

    public function submitReport(Request $request, StaffRequest $staffRequest)
    {
        $employee = auth()->user()->employee;
        if (!$employee || $staffRequest->employee_id !== $employee->id) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'report_summary' => 'required|string|min:20|max:5000',
            'report_action_plan' => 'required|string|min:10|max:3000',
            'certificate_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'materials_file' => 'nullable|file|mimes:pdf,zip,ppt,pptx,doc,docx|max:20480',
        ]);

        $this->service->submitSeminarReport(
            $staffRequest,
            $validated,
            $request->file('certificate_file'),
            $request->file('materials_file')
        );

        return redirect()->route('portal.requests.show', $staffRequest)
            ->with('success', 'Your Seminar Report has been submitted successfully to HR! It is now pending HR review.');
    }
}
