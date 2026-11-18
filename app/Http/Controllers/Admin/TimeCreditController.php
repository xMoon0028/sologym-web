<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\TimeCreditLedger;
use App\Services\TimeCreditService;
use Illuminate\Http\Request;

class TimeCreditController extends Controller
{
    public function __construct(private TimeCreditService $service) {}

    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'balances');

        // 1. Credit Balances query & pagination
        $employeesQuery = Employee::with(['department', 'timeCreditLedger'])
            ->where('is_active', true);

        if ($request->filled('balance_search')) {
            $search = trim($request->balance_search);
            $employeesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_no', 'like', "%{$search}%");
            });
        } elseif ($request->filled('search')) {
            $search = trim($request->search);
            $employeesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('dept')) {
            $employeesQuery->where('department_id', $request->dept);
        }

        if ($request->filled('balance_filter')) {
            if ($request->balance_filter === 'positive') {
                $employeesQuery->whereHas('timeCreditLedger', function ($q) {
                    $q->where('status', 'approved');
                });
            } elseif ($request->balance_filter === 'pending') {
                $employeesQuery->whereHas('timeCreditLedger', function ($q) {
                    $q->where('status', 'pending');
                });
            }
        }

        $employees = $employeesQuery->orderBy('name')
            ->paginate(12, ['*'], 'balance_page')
            ->withQueryString();

        $employees->getCollection()->transform(function ($emp) {
            $emp->balance = $this->service->getBalance($emp);
            $emp->pending_hours = $this->service->getPendingHours($emp);
            return $emp;
        });

        // 2. Pending Verifications query & pagination
        $pendingQuery = TimeCreditLedger::with(['employee.department', 'attendanceRecord'])
            ->where('status', 'pending');

        if ($request->filled('pending_search')) {
            $pSearch = trim($request->pending_search);
            $pendingQuery->where(function ($q) use ($pSearch) {
                $q->where('reason', 'like', "%{$pSearch}%")
                  ->orWhereHas('employee', function ($eq) use ($pSearch) {
                      $eq->where('name', 'like', "%{$pSearch}%")
                        ->orWhere('employee_no', 'like', "%{$pSearch}%");
                  });
            });
        }

        if ($request->filled('pending_dept')) {
            $pendingDept = $request->pending_dept;
            $pendingQuery->whereHas('employee', function ($eq) use ($pendingDept) {
                $eq->where('department_id', $pendingDept);
            });
        }

        $pendingVerifications = $pendingQuery->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10, ['*'], 'pending_page')
            ->withQueryString();

        // 3. Metadata for filters and modal
        $departments = \App\Models\Department::orderBy('name')->get();
        $allEmployees = Employee::where('is_active', true)->orderBy('name')->get(['id', 'name', 'department_id']);
        $totalEmployeesCount = Employee::where('is_active', true)->count();
        $pendingCount = TimeCreditLedger::where('status', 'pending')->count();

        return view('admin.time-credits.index', compact(
            'employees',
            'pendingVerifications',
            'departments',
            'allEmployees',
            'totalEmployeesCount',
            'pendingCount',
            'activeTab'
        ));
    }

    public function show(Employee $employee)
    {
        $ledger = TimeCreditLedger::with(['creator', 'approver', 'attendanceRecord'])
            ->where('employee_id', $employee->id)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(30);

        $balance = $this->service->getBalance($employee);
        $pendingHours = $this->service->getPendingHours($employee);

        return view('admin.time-credits.show', compact('employee', 'ledger', 'balance', 'pendingHours'));
    }

    /**
     * Integrated Adjustment directly from Offset Time Credits module.
     */
    public function storeAdjustment(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type'        => 'required|in:add,deduct',
            'hours'       => 'required|numeric|gt:0',
            'reason'      => 'required|string|max:255',
            'date'        => 'nullable|date',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $hours = (float) $request->hours;
        if ($request->type === 'deduct') {
            $hours = -$hours;
        }

        $this->service->adjust($employee, $hours, $request->reason, $request->date);

        $actionText = $request->type === 'add' ? 'granted' : 'deducted';
        return back()->with('success', "Successfully {$actionText} {$request->hours} time credit hours for {$employee->name}.");
    }

    /**
     * Direct employee adjustment route.
     */
    public function adjust(Request $request, Employee $employee)
    {
        $request->validate([
            'hours' => 'required|numeric',
            'reason' => 'required|string|max:255',
            'date' => 'nullable|date',
        ]);

        $this->service->adjust($employee, (float) $request->hours, $request->reason, $request->date);
        return back()->with('success', "Time credit adjusted for {$employee->name}.");
    }

    /**
     * Approve a pending overtime/weekend credit.
     */
    public function approve(TimeCreditLedger $entry)
    {
        $this->service->approve($entry, auth()->id());
        return back()->with('success', "Time credit of {$entry->hours} hrs approved for {$entry->employee->name}.");
    }

    /**
     * Reject a pending overtime/weekend credit.
     */
    public function reject(Request $request, TimeCreditLedger $entry)
    {
        $this->service->reject($entry, $request->reason, auth()->id());
        return back()->with('success', "Time credit of {$entry->hours} hrs rejected for {$entry->employee->name}.");
    }

    /**
     * Approve all pending time credit verifications in batch.
     */
    public function approveAll()
    {
        $count = $this->service->approveAllPending(auth()->id());
        return back()->with('success', "Successfully approved {$count} pending time credit records.");
    }

    public function processAttendance(Request $request, Employee $employee)
    {
        $records = $employee->attendanceRecords()->whereDoesntHave('timeCreditEntry')->get();
        $count = 0;
        foreach ($records as $record) {
            if ($this->service->processRecord($record)) $count++;
        }
        return back()->with('success', "Scanned and submitted {$count} attendance records for HR time credit verification.");
    }
}
