<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Services\LeaveService;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function __construct(protected LeaveService $leaveService)
    {
    }

    public function index(Request $request)
    {
        // Auto-expire any overdue leave requests past their 1-day stage limit
        $this->leaveService->checkAndExpireOverdueRequests();

        $stage = $request->query('stage', 'all');

        $query = LeaveRequest::with(['employee.department', 'approvals.approver', 'rejector'])
            ->latest('created_at');

        if ($stage === 'section_head') {
            $query->where('status', 'pending_section_head');
        } elseif ($stage === 'executive_office') {
            $query->where('status', 'pending_executive');
        } elseif ($stage === 'executive_director') {
            $query->where('status', 'pending_executive_director');
        } elseif ($stage === 'approved') {
            $query->where('status', 'approved');
        } elseif ($stage === 'rejected') {
            $query->where('status', 'rejected');
        } elseif ($stage === 'expired') {
            $query->where('status', 'expired');
        }

        if ($request->filled('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $leaves = $query->paginate(20)->withQueryString();

        // Metrics for tabs (single aggregated query for high performance)
        $tabStats = LeaveRequest::selectRaw("
            COUNT(*) as all_count,
            COUNT(CASE WHEN status = 'pending_section_head' THEN 1 END) as pending_section,
            COUNT(CASE WHEN status = 'pending_executive' THEN 1 END) as pending_exec,
            COUNT(CASE WHEN status = 'pending_executive_director' THEN 1 END) as pending_director,
            COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_count,
            COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_count,
            COUNT(CASE WHEN status = 'expired' THEN 1 END) as expired_count
        ")->first();

        $totalCount      = (int) ($tabStats->all_count ?? 0);
        $pendingSection  = (int) ($tabStats->pending_section ?? 0);
        $pendingExec     = (int) ($tabStats->pending_exec ?? 0);
        $pendingDirector = (int) ($tabStats->pending_director ?? 0);
        $approvedCount   = (int) ($tabStats->approved_count ?? 0);
        $rejectedCount   = (int) ($tabStats->rejected_count ?? 0);
        $expiredCount    = (int) ($tabStats->expired_count ?? 0);

        $counts = [
            'all'                        => $totalCount,
            'pending_section_head'       => $pendingSection,
            'pending_executive'          => $pendingExec,
            'pending_executive_director' => $pendingDirector,
            'approved'                   => $approvedCount,
            'rejected'                   => $rejectedCount,
            'expired'                    => $expiredCount,
        ];

        $requests = $leaves;
        $tab = $stage;

        $employees = Employee::where('is_active', true)->orderBy('name')->get();

        return view('admin.leaves.index', compact(
            'leaves',
            'requests',
            'stage',
            'tab',
            'counts',
            'pendingSection',
            'pendingExec',
            'pendingDirector',
            'approvedCount',
            'rejectedCount',
            'expiredCount',
            'totalCount',
            'employees'
        ));
    }

    public function create()
    {
        $employees = Employee::where('is_active', true)->with('department')->orderBy('name')->get();
        return view('admin.leaves.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $leaveType = $request->input('leave_type');
        if ($leaveType === 'parental') $leaveType = 'solo_parent';
        if ($leaveType === 'bwl') $leaveType = 'vawc';
        if (in_array($leaveType, ['gl', 'special_women'])) $leaveType = 'gynecological';
        if ($leaveType === 'offset') $leaveType = 'time_off';
        $request->merge(['leave_type' => $leaveType]);

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'required|string|in:vacation,sick,emergency,maternity,paternity,solo_parent,parental,vawc,bwl,gynecological,special_women,gl,time_off,offset,bereavement,unpaid',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'days_count' => 'required|numeric|min:0.5',
            'is_half_day' => 'nullable|boolean',
            'half_day_type' => 'nullable|in:first_half,second_half',
            'reason' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave-attachments', 'public');
            $validated['attachment'] = $attachmentPath;
        }

        $leave = $this->leaveService->createManualRequestByHr($employee, $validated, auth()->user());

        return redirect()->route('admin.leaves.show', $leave)->with('success', "Leave successfully encoded and recorded for {$employee->name}.");
    }

    public function show(LeaveRequest $leave)
    {
        $this->leaveService->checkAndExpireOverdueRequests();
        $leave->refresh();

        $leave->load(['employee.department', 'employee.user', 'approvals.approver', 'rejector']);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'leave' => $leave,
                'approvals' => $leave->approvals,
                'employee' => $leave->employee,
            ]);
        }

        return view('admin.leaves.show', compact('leave'));
    }

    public function approve(Request $request, LeaveRequest $leave)
    {
        $this->leaveService->checkAndExpireOverdueRequests();
        $leave->refresh();

        if ($leave->status === 'expired') {
            abort(400, 'This leave request has expired because the 1-day approval time limit was exceeded.');
        }

        $user = auth()->user();
        $employee = $user->employee;
        $isSuperAdmin = (!$employee && $user->email === 'admin@lasac.com');

        $stage = $request->input('override_stage') ?: ($request->input('stage') ?: ($leave->current_stage ?: 'section_head'));
        if ($stage === 'executive') {
            $stage = 'executive_office';
        }

        if ($stage === 'hr') {
            abort(403, 'HR is no longer part of the leave approval process. HR receives leave records and email notifications for monitoring only.');
        }

        // Strict role-based authority:
        if (!$isSuperAdmin) {
            if (!$employee) {
                abort(403, 'Your account is not linked to an employee with leave approval authority.');
            }

            if ($employee->id === $leave->employee_id) {
                abort(403, 'You cannot approve or review your own leave request.');
            }

            if ($stage === 'section_head') {
                if ($employee->approval_role !== 'section_head' || $employee->department_id !== $leave->employee->department_id || !in_array($leave->employee->approval_role, ['none', 'hr_officer'])) {
                    abort(403, 'Only designated Section Heads can approve regular staff leave requests for their department.');
                }
            } elseif ($stage === 'executive_office') {
                if (!in_array($employee->approval_role, ['executive_office', 'executive_director'])) {
                    abort(403, 'Only the Executive Office can grant final leave approvals.');
                }
            } elseif ($stage === 'executive_director') {
                if ($employee->approval_role !== 'executive_director') {
                    abort(403, 'Only the Executive Director can approve leave requests filed by the Executive Office.');
                }
            } else {
                abort(403, 'Invalid leave approval stage.');
            }
        }

        $rawAction = strtolower((string) $request->input('action'));
        if (in_array($rawAction, ['approve', 'approved', 'recommend', 'recommended'])) {
            $action = 'approved';
        } elseif (in_array($rawAction, ['reject', 'rejected', 'disapprove', 'disapproved'])) {
            $action = 'rejected';
        } else {
            $action = $rawAction;
        }

        $request->merge([
            'stage' => $stage,
            'action' => $action,
        ]);

        $validated = $request->validate([
            'stage' => 'required|in:section_head,executive_office,executive_director',
            'action' => 'required|in:approved,rejected',
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->leaveService->processApproval(
            $leave,
            auth()->user(),
            $validated['stage'],
            $validated['action'],
            $validated['notes'] ?? null
        );

        $actionText = $validated['action'] === 'rejected' ? 'disapproved' : 'approved';

        if ($request->header('referer') && str_contains($request->header('referer'), '/leaves/' . $leave->id)) {
            return back()->with('success', "Leave request has been successfully {$actionText}.");
        }

        return redirect()->route('admin.leaves.index')->with('success', "Leave request has been successfully {$actionText}.");
    }
}
