<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\LeaveService;
use Illuminate\Http\Request;

class EmployeeLeaveController extends Controller
{
    public function __construct(protected LeaveService $leaveService)
    {
    }

    public function index(Request $request)
    {
        $employee = auth()->user()->employee;
        if (!$employee) {
            return view('portal.no-profile');
        }

        // Auto-expire any overdue leave requests past their 1-day stage limit
        $this->leaveService->checkAndExpireOverdueRequests();

        $myLeaves = LeaveRequest::where('employee_id', $employee->id)
            ->with(['approvals.approver'])
            ->latest('created_at')
            ->paginate(15);

        // Role-based approver determination
        $isSectionHead = ($employee->approval_role === 'section_head');
        $isHrOfficer = ($employee->approval_role === 'hr_officer');
        $isExecutive = in_array($employee->approval_role, ['executive_office', 'executive_director']);
        $isExecutiveDirector = ($employee->approval_role === 'executive_director');

        $sectionPending = collect();
        if ($isSectionHead) {
            $sectionPending = LeaveRequest::whereHas('employee', function ($q) use ($employee) {
                $q->where('department_id', $employee->department_id)
                  ->where('id', '!=', $employee->id)
                  ->whereIn('approval_role', ['none', 'hr_officer']);
            })
            ->where('status', 'pending_section_head')
            ->with(['employee.department', 'approvals.approver'])
            ->latest('created_at')
            ->get();
        }

        // HR is no longer part of the approval chain (monitoring & records only)
        $hrPending = collect();

        $executivePending = collect();
        if ($isExecutiveDirector) {
            $executivePending = LeaveRequest::whereIn('status', ['pending_executive', 'pending_executive_director'])
                ->where('employee_id', '!=', $employee->id)
                ->with(['employee.department', 'approvals.approver'])
                ->latest('created_at')
                ->get();
        } elseif ($employee->approval_role === 'executive_office') {
            $executivePending = LeaveRequest::where('status', 'pending_executive')
                ->where('employee_id', '!=', $employee->id)
                ->with(['employee.department', 'approvals.approver'])
                ->latest('created_at')
                ->get();
        }

        $requests = $myLeaves;
        $pendingDepartmentRequests = $sectionPending;
        $pendingHrRequests = $hrPending;
        $pendingExecutiveRequests = $executivePending;

        return view('portal.leaves.index', compact(
            'employee',
            'myLeaves',
            'requests',
            'isSectionHead',
            'isHrOfficer',
            'isExecutive',
            'isExecutiveDirector',
            'sectionPending',
            'pendingDepartmentRequests',
            'pendingHrRequests',
            'pendingExecutiveRequests'
        ));
    }

    public function store(Request $request)
    {
        $employee = auth()->user()->employee;
        if (!$employee) {
            return back()->with('error', 'Employee record not found.');
        }

        // Normalize leave_type (support 'vacation_leave', 'vacation', etc.)
        $leaveType = $request->input('leave_type') ?? $request->input('type');
        $leaveType = str_replace('_leave', '', (string) $leaveType);

        // Normalize synonyms
        if ($leaveType === 'parental') $leaveType = 'solo_parent';
        if ($leaveType === 'bwl') $leaveType = 'vawc';
        if (in_array($leaveType, ['gl', 'special_women'])) $leaveType = 'gynecological';
        if ($leaveType === 'offset') $leaveType = 'time_off';

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $isHalfDay = filter_var($request->input('is_half_day'), FILTER_VALIDATE_BOOLEAN);
        $halfDayType = $request->input('half_day_type') ?? $request->input('half_day_session');

        // Auto-calculate days_count if not explicitly passed
        $daysCount = $request->input('days_count');
        if (empty($daysCount) && $startDate && $endDate) {
            try {
                $start = \Carbon\Carbon::parse($startDate);
                $end = \Carbon\Carbon::parse($endDate);
                $daysCount = $isHalfDay ? 0.5 : (max(1, $start->diffInDays($end) + 1));
            } catch (\Exception) {
                $daysCount = 1.0;
            }
        }

        $request->merge([
            'leave_type' => $leaveType,
            'days_count' => $daysCount,
            'is_half_day' => $isHalfDay ? 1 : 0,
            'half_day_type' => $halfDayType,
        ]);

        $validated = $request->validate([
            'leave_type' => 'required|string|in:vacation,sick,emergency,maternity,paternity,solo_parent,vawc,gynecological,time_off,offset,parental,bwl,gl,special_women,bereavement,unpaid',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'days_count' => 'required|numeric|min:0.5',
            'is_half_day' => 'nullable|boolean',
            'half_day_type' => 'nullable|in:morning,afternoon,first_half,second_half',
            'reason' => 'required|string|min:3|max:1000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('attachment')) {
            $validated['attachment'] = $request->file('attachment')->store('leave-attachments', 'public');
        }

        $this->leaveService->createRequest($employee, $validated);

        $routeTarget = match ($employee->approval_role) {
            'section_head' => 'the Executive Office',
            'executive_office', 'executive_director' => 'the Executive Director',
            default => 'your Section Head',
        };

        return redirect()->route('portal.leaves.index')->with(
            'success',
            "Your leave request has been submitted and routed to {$routeTarget} for review (1-day approval window)."
        );
    }

    public function show(LeaveRequest $leave)
    {
        $this->leaveService->checkAndExpireOverdueRequests();
        $leave->refresh();

        $employee = auth()->user()->employee;
        $isSuperAdmin = (!auth()->user()->employee && auth()->user()->email === 'admin@lasac.com');

        // Role-based visibility (HR sees all leave records even though not in approval chain):
        $isOwner = $employee && $employee->id === $leave->employee_id;
        $isSectionHead = $employee && $employee->approval_role === 'section_head'
            && $employee->department_id === $leave->employee->department_id
            && in_array($leave->employee->approval_role, ['none', 'hr_officer'])
            && $employee->id !== $leave->employee_id;
        $isHrOfficer = $employee && $employee->approval_role === 'hr_officer';
        $isExecutive = $employee && in_array($employee->approval_role, ['executive_office', 'executive_director']) && $employee->id !== $leave->employee_id;

        if (!$isOwner && !$isSectionHead && !$isHrOfficer && !$isExecutive && !$isSuperAdmin) {
            abort(403, 'Unauthorized access to this leave request.');
        }

        $leave->load(['employee.department', 'approvals.approver', 'rejector']);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'leave' => $leave,
                'approvals' => $leave->approvals,
            ]);
        }

        return view('portal.leaves.show', compact('leave', 'employee', 'isSectionHead', 'isHrOfficer', 'isExecutive'));
    }

    /**
     * Section head action from the employee portal (1-day limit).
     */
    public function sectionApprove(Request $request, LeaveRequest $leave)
    {
        $this->leaveService->checkAndExpireOverdueRequests();
        $leave->refresh();

        $employee = auth()->user()->employee;
        $isSuperAdmin = (!auth()->user()->employee && auth()->user()->email === 'admin@lasac.com');

        $isAuthorizedSectionHead = $employee
            && $employee->approval_role === 'section_head'
            && $employee->department_id === $leave->employee->department_id
            && in_array($leave->employee->approval_role, ['none', 'hr_officer'])
            && $employee->id !== $leave->employee_id;

        if (!$isAuthorizedSectionHead && !$isSuperAdmin) {
            abort(403, 'Only designated Section Heads can approve regular staff leave requests for their department.');
        }

        if ($leave->status === 'expired') {
            abort(400, 'This leave request has expired after exceeding the 1-day approval limit.');
        }

        if ($leave->status !== 'pending_section_head') {
            abort(400, 'This leave request is not pending Section Head approval.');
        }

        $action = $request->input('action');
        if (in_array($action, ['approve', 'approved'])) {
            $action = 'approved';
        }
        if (in_array($action, ['reject', 'rejected', 'disapprove', 'disapproved'])) {
            $action = 'rejected';
        }
        $request->merge(['action' => $action]);

        $validated = $request->validate([
            'action' => 'required|in:approved,rejected',
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->leaveService->processApproval(
            $leave,
            auth()->user(),
            'section_head',
            $validated['action'],
            $validated['notes'] ?? null
        );

        $actionText = $validated['action'] === 'rejected' ? 'disapproved' : 'approved';
        if ($request->header('referer') && str_contains($request->header('referer'), '/leaves/' . $leave->id)) {
            return back()->with('success', "Leave request {$actionText} successfully by Section Head.");
        }
        return redirect()->route('portal.leaves.index')->with('success', "Leave request {$actionText} successfully by Section Head.");
    }

    /**
     * HR Officer endpoint — HR is no longer part of the approval process.
     */
    public function hrApprove(Request $request, LeaveRequest $leave)
    {
        abort(403, 'HR is no longer part of the leave approval process. HR receives leave records and email notifications for monitoring only.');
    }

    /**
     * Executive Office / Executive Director action from the employee portal (1-day limit).
     */
    public function executiveApprove(Request $request, LeaveRequest $leave)
    {
        $this->leaveService->checkAndExpireOverdueRequests();
        $leave->refresh();

        $employee = auth()->user()->employee;
        $isSuperAdmin = (!auth()->user()->employee && auth()->user()->email === 'admin@lasac.com');

        if ($leave->status === 'expired') {
            abort(400, 'This leave request has expired after exceeding the 1-day approval limit.');
        }

        $targetStage = $leave->status === 'pending_executive_director' ? 'executive_director' : 'executive_office';

        if ($targetStage === 'executive_director') {
            $isAuthorizedExec = $employee && $employee->approval_role === 'executive_director' && $employee->id !== $leave->employee_id;
            if (!$isAuthorizedExec && !$isSuperAdmin) {
                abort(403, 'Only the Executive Director can approve leave requests filed by the Executive Office.');
            }
        } else {
            $isAuthorizedExec = $employee && in_array($employee->approval_role, ['executive_office', 'executive_director']) && $employee->id !== $leave->employee_id;
            if (!$isAuthorizedExec && !$isSuperAdmin) {
                abort(403, 'Only the Executive Office can perform final leave approvals.');
            }
            if ($leave->status !== 'pending_executive') {
                abort(400, 'This leave request is not pending Executive Office final approval.');
            }
        }

        $rawAction = strtolower((string) $request->input('action'));
        if (in_array($rawAction, ['approve', 'approved'])) {
            $action = 'approved';
        } elseif (in_array($rawAction, ['reject', 'rejected', 'disapprove', 'disapproved'])) {
            $action = 'rejected';
        } else {
            $action = $rawAction;
        }
        $request->merge(['action' => $action]);

        $validated = $request->validate([
            'action' => 'required|in:approved,rejected',
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->leaveService->processApproval(
            $leave,
            auth()->user(),
            $targetStage,
            $validated['action'],
            $validated['notes'] ?? null
        );

        $roleTitle = $targetStage === 'executive_director' ? 'Executive Director' : 'Executive Office';
        $actionText = $validated['action'] === 'rejected' ? 'disapproved' : 'officially approved';
        if ($request->header('referer') && str_contains($request->header('referer'), '/leaves/' . $leave->id)) {
            return back()->with('success', "Leave request has been {$actionText} by the {$roleTitle}.");
        }
        return redirect()->route('portal.leaves.index')->with('success', "Leave request has been {$actionText} by the {$roleTitle}.");
    }
}
