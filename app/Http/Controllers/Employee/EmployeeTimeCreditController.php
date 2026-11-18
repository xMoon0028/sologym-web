<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\TimeCreditLedger;
use App\Services\TimeCreditService;

class EmployeeTimeCreditController extends Controller
{
    public function __construct(private TimeCreditService $service) {}

    public function index()
    {
        $employee = auth()->user()->employee;
        if (!$employee) return view('portal.no-profile');

        $balance = $this->service->getBalance($employee);
        $pendingHours = $this->service->getPendingHours($employee);
        $ledger = TimeCreditLedger::where('employee_id', $employee->id)
            ->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate(30);

        return view('portal.time-credits.index', compact('employee', 'balance', 'pendingHours', 'ledger'));
    }
}
