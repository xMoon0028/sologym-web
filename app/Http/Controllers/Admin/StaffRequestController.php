<?php

namespace App\Http\Controllers\Admin;

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
        $query = StaffRequest::with(['employee.department', 'reviewer', 'approvals']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->whereIn('status', ['pending', 'pending_section_head', 'pending_executive', 'pending_executive_director']);
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->whereHas('employee', function ($eq) use ($s) {
                    $eq->where('name', 'like', "%{$s}%")
                       ->orWhere('employee_no', 'like', "%{$s}%");
                })->orWhere('seminar_title', 'like', "%{$s}%")
                  ->orWhere('document_type', 'like', "%{$s}%");
            });
        }

        $requests = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total_pending' => StaffRequest::whereIn('status', ['pending', 'pending_section_head', 'pending_executive', 'pending_executive_director'])->count(),
            'pending_seminars' => StaffRequest::where('type', 'seminar')->whereIn('status', ['pending', 'pending_section_head', 'pending_executive', 'pending_executive_director'])->count(),
            'pending_reports' => StaffRequest::where('type', 'seminar')->where('status', 'report_pending')->count(),
            'pending_documents' => StaffRequest::where('type', 'document')->where('status', 'pending')->count(),
        ];

        return view('admin.requests.index', compact('requests', 'stats'));
    }

    public function show(StaffRequest $staffRequest)
    {
        $staffRequest->load(['employee.department', 'employee.user', 'reviewer', 'reportAcknowledger', 'approvals.approver']);
        return view('admin.requests.show', compact('staffRequest'));
    }

    public function approve(Request $request, StaffRequest $staffRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
            'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($staffRequest->is_seminar) {
            $stage = in_array($staffRequest->status, ['pending_executive', 'pending_executive_director'])
                ? ($staffRequest->status === 'pending_executive_director' ? 'executive_director' : 'executive_office')
                : 'section_head';

            $this->service->processSeminarApproval(
                $staffRequest,
                auth()->user(),
                $stage,
                'approved',
                $request->admin_notes
            );

            $stageLabel = $stage === 'section_head' ? 'endorsed by Section Head and forwarded to Executive Office' : 'officially approved by Executive Office';
            return redirect()->route('admin.requests.show', $staffRequest)->with('success', "Seminar request has been {$stageLabel}.");
        }

        $this->service->approveRequest(
            $staffRequest,
            auth()->user(),
            $request->admin_notes,
            $request->file('document_file')
        );

        return redirect()->route('admin.requests.show', $staffRequest)->with('success', 'Document request approved and fulfilled.');
    }

    public function reject(Request $request, StaffRequest $staffRequest)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($staffRequest->is_seminar) {
            $stage = in_array($staffRequest->status, ['pending_executive', 'pending_executive_director'])
                ? ($staffRequest->status === 'pending_executive_director' ? 'executive_director' : 'executive_office')
                : 'section_head';

            $this->service->processSeminarApproval(
                $staffRequest,
                auth()->user(),
                $stage,
                'rejected',
                $request->rejection_reason
            );

            return redirect()->route('admin.requests.show', $staffRequest)->with('success', 'Seminar request has been disapproved and employee notified.');
        }

        $this->service->rejectRequest($staffRequest, auth()->user(), $request->rejection_reason);

        return redirect()->route('admin.requests.show', $staffRequest)->with('success', 'Request has been disapproved and employee notified.');
    }

    public function acknowledgeReport(Request $request, StaffRequest $staffRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $this->service->acknowledgeSeminarReport($staffRequest, auth()->user(), $request->admin_notes);

        return redirect()->route('admin.requests.show', $staffRequest)->with('success', 'Seminar Report acknowledged. Training credentials successfully logged for employee.');
    }
}
