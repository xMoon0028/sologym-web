<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\DisciplinaryNotice;
use App\Models\Employee;
use App\Services\DisciplinaryService;
use Illuminate\Http\Request;

class EmployeeDisciplinaryController extends Controller
{
    public function __construct(
        protected DisciplinaryService $disciplinaryService
    ) {}

    public function index(Request $request)
    {
        $employee = auth()->user()->employee;
        if (!$employee && auth()->user()->email) {
            $employee = Employee::where('email', auth()->user()->email)->first();
        }

        if (!$employee) {
            return redirect()->route('portal.dashboard')
                ->with('error', 'Your user account is not linked to an employee profile.');
        }

        $notices = DisciplinaryNotice::where('employee_id', $employee->id)
            ->with('issuer')
            ->latest('id')
            ->paginate(15);

        $totalNotices = DisciplinaryNotice::where('employee_id', $employee->id)->count();

        $pendingExplanationCount = DisciplinaryNotice::where('employee_id', $employee->id)
            ->where('requires_explanation', true)
            ->whereNull('scanned_nte_path')
            ->whereNull('nte_received_at')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();

        $overdueCount = DisciplinaryNotice::where('employee_id', $employee->id)
            ->where('requires_explanation', true)
            ->whereNull('scanned_nte_path')
            ->whereNull('nte_received_at')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->whereDate('explanation_deadline', '<', now()->toDateString())
            ->count();

        $resolvedCount = DisciplinaryNotice::where('employee_id', $employee->id)
            ->whereIn('status', ['resolved', 'closed'])
            ->count();

        $stats = [
            'total' => $totalNotices,
            'pending' => $pendingExplanationCount,
            'overdue' => $overdueCount,
            'resolved' => $resolvedCount,
        ];

        return view('portal.disciplinary.index', compact('employee', 'notices', 'pendingExplanationCount', 'stats'));
    }

    public function show(DisciplinaryNotice $conduct)
    {
        $employee = auth()->user()->employee;
        if (!$employee && auth()->user()->email) {
            $employee = Employee::where('email', auth()->user()->email)->first();
        }

        if (!$employee || $conduct->employee_id !== $employee->id) {
            abort(403, 'Unauthorized access to this disciplinary notice.');
        }

        $this->disciplinaryService->markAsViewed($conduct);
        $conduct->load(['issuer', 'resolver']);

        return view('portal.disciplinary.show', ['notice' => $conduct, 'employee' => $employee]);
    }

    public function submitExplanation(Request $request, DisciplinaryNotice $conduct)
    {
        $employee = auth()->user()->employee;
        if (!$employee && auth()->user()->email) {
            $employee = Employee::where('email', auth()->user()->email)->first();
        }

        if (!$employee || $conduct->employee_id !== $employee->id) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'employee_explanation' => 'required|string|min:10|max:5000',
            'explanation_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        $this->disciplinaryService->submitExplanation(
            $conduct,
            $validated['employee_explanation'],
            $request->file('explanation_attachment')
        );

        return redirect()->route('portal.conduct.show', $conduct)
            ->with('success', 'Your formal written explanation has been submitted successfully to the HR Officer.');
    }
}
