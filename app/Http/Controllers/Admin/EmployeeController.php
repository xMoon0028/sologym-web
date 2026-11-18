<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\EmployeeImport;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeAddress;
use App\Models\EmployeeCompensation;
use App\Models\EmployeeEducation;
use App\Models\EmployeeFamily;
use App\Models\EmployeeGovernmentId;
use App\Models\EmployeeIq;
use App\Models\EmployeeLastEmployment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

use App\Services\SpreadsheetImportService;

class EmployeeController extends Controller
{
    public function __construct(protected SpreadsheetImportService $importService)
    {
    }

    public function index(Request $request)
    {
        $view = $request->query('view', 'active');
        if (!in_array($view, ['active', 'inactive', 'all'])) {
            $view = 'active';
        }

        $activeCount = Employee::where('is_active', true)->count();
        $inactiveCount = Employee::where('is_active', false)->count();
        $totalCount = Employee::count();

        $employees = Employee::with('department')
            ->when($request->search, fn($q) => $q->where(function ($sub) use ($request) {
                $sub->where('name', 'like', "%{$request->search}%")
                    ->orWhere('employee_no', 'like', "%{$request->search}%");
            }))
            ->when($request->department, fn($q) => $q->where('department_id', $request->department))
            ->when($request->status, fn($q) => $q->where('employment_status', $request->status))
            ->when($view === 'active', fn($q) => $q->where('is_active', true))
            ->when($view === 'inactive', fn($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $departments = Department::orderBy('name')->get();
        return view('admin.employees.index', compact('employees', 'departments', 'view', 'activeCount', 'inactiveCount', 'totalCount'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('admin.employees.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:employees,email',
            'employee_no' => 'nullable|string|unique:employees,employee_no',
            'date_hired' => 'nullable|date',
            'birthdate' => 'nullable|date',
            'photo' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('employee-photos', 'public');
            }

            $workEmail = $request->filled('email') ? strtolower(trim($request->email)) : null;

            $employee = Employee::create([
                'name' => $request->name,
                'email' => $workEmail,
                'employee_no' => $request->employee_no,
                'position' => $request->position,
                'salary_grade' => $request->salary_grade,
                'employment_status' => $request->employment_status,
                'department_id' => $request->department_id,
                'date_hired' => $request->date_hired,
                'birthdate' => $request->birthdate,
                'sex' => $request->sex,
                'civil_status' => $request->civil_status,
                'photo' => $photoPath,
                'is_active' => true,
                'approval_role' => $request->approval_role ?? 'none',
                'vacation_leave_credits' => $request->vacation_leave_credits ?? 15.0,
                'sick_leave_credits' => $request->sick_leave_credits ?? 15.0,
                'emergency_leave_credits' => $request->emergency_leave_credits ?? 3.0,
            ]);

            // Link or create user account if email is provided
            if ($workEmail) {
                $user = User::where('email', $workEmail)->first();
                if (!$user) {
                    $user = User::create([
                        'name' => $employee->name,
                        'email' => $workEmail,
                        'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(32)),
                    ]);
                    $user->assignRole('employee');
                }
                $employee->update(['user_id' => $user->id]);
                $employee->syncUserAdminRole();
            }

            EmployeeAddress::create([
                'employee_id' => $employee->id,
                'region' => $request->region,
                'province' => $request->province,
                'municipality_city' => $request->municipality_city,
                'barangay' => $request->barangay,
                'permanent_address' => $request->permanent_address,
                'current_address' => $request->current_address,
                'zip_code' => $request->zip_code,
            ]);

            EmployeeGovernmentId::create([
                'employee_id' => $employee->id,
                'sss_no' => $request->sss_no,
                'pagibig_no' => $request->pagibig_no,
                'philhealth_no' => $request->philhealth_no,
                'tin_no' => $request->tin_no
            ]);

            EmployeeCompensation::create([
                'employee_id' => $employee->id,
                'basic_salary_80' => $request->basic_salary_80 ?? 0,
                'allowance_20' => $request->allowance_20 ?? 0,
                'total_compensation' => $request->total_compensation ?? 0,
                'sss_deduction' => $request->sss_deduction ?? 0,
                'ph_deduction' => $request->ph_deduction ?? 0,
                'pi_deduction' => $request->pi_deduction ?? 0
            ]);

            EmployeeEducation::create([
                'employee_id' => $employee->id,
                'primary_school' => $request->primary_school,
                'secondary_school' => $request->secondary_school
            ]);

            EmployeeIq::create([
                'employee_id' => $employee->id,
                'total_stanine' => $request->total_stanine,
                'verbal' => $request->verbal,
                'non_verbal' => $request->non_verbal,
                'holland_personality' => $request->holland_personality
            ]);

            EmployeeLastEmployment::create([
                'employee_id' => $employee->id,
                'company' => $request->prev_company,
                'position' => $request->prev_position,
                'last_salary' => $request->last_salary,
                'reason_for_leaving' => $request->reason_for_leaving
            ]);

            EmployeeFamily::create([
                'employee_id' => $employee->id,
                'marriage_type' => $request->marriage_type,
                'spouse_occupation' => $request->spouse_occupation,
                'no_of_siblings' => $request->no_of_siblings ?? 0,
                'no_of_children' => $request->no_of_children ?? 0,
                'dependents' => $request->dependents,
                'single_status' => $request->single_status
            ]);

            DB::commit();
            return redirect()->route('admin.employees.index')->with('success', 'Employee added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error saving employee: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Employee $employee)
    {
        return $this->profile($employee);
    }

    public function profile(Employee $employee)
    {
        $employee->load([
            'department', 'address', 'governmentIds', 'compensation',
            'education', 'iq', 'lastEmployment', 'family',
            'certificates' => fn($q) => $q->orderBy('date_issued', 'desc'),
            'attendanceRecords' => fn($q) => $q->orderBy('date', 'desc')->take(30),
            'timeCreditLedger' => fn($q) => $q->orderBy('date', 'desc')->take(20),
            'payslips.period',
        ]);
        return view('admin.employees.profile', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $employee->load(['address', 'governmentIds', 'compensation', 'education', 'iq', 'lastEmployment', 'family']);
        $departments = Department::orderBy('name')->get();
        return view('admin.employees.edit', compact('employee', 'departments'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:employees,email,' . $employee->id,
            'employee_no' => 'nullable|string|unique:employees,employee_no,' . $employee->id,
            'photo' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $oldVl = (float)($employee->vacation_leave_credits ?? 15.0);
            $oldSl = (float)($employee->sick_leave_credits ?? 15.0);
            $oldEl = (float)($employee->emergency_leave_credits ?? 3.0);
            $oldCompensation = (float)($employee->compensation?->total_compensation ?? 0.0);

            $photoPath = $employee->photo;
            if ($request->hasFile('photo')) {
                if ($photoPath) Storage::disk('public')->delete($photoPath);
                $photoPath = $request->file('photo')->store('employee-photos', 'public');
            }

            $workEmail = $request->filled('email') ? strtolower(trim($request->email)) : null;

            $employee->update([
                'name' => $request->name,
                'email' => $workEmail,
                'employee_no' => $request->employee_no,
                'position' => $request->position,
                'salary_grade' => $request->salary_grade,
                'employment_status' => $request->employment_status,
                'department_id' => $request->department_id,
                'date_hired' => $request->date_hired,
                'birthdate' => $request->birthdate,
                'sex' => $request->sex,
                'civil_status' => $request->civil_status,
                'photo' => $photoPath,
                'approval_role' => $request->approval_role ?? $employee->approval_role ?? 'none',
                'vacation_leave_credits' => $request->filled('vacation_leave_credits') ? $request->vacation_leave_credits : ($employee->vacation_leave_credits ?? 15.0),
                'sick_leave_credits' => $request->filled('sick_leave_credits') ? $request->sick_leave_credits : ($employee->sick_leave_credits ?? 15.0),
                'emergency_leave_credits' => $request->filled('emergency_leave_credits') ? $request->emergency_leave_credits : ($employee->emergency_leave_credits ?? 3.0),
            ]);

            // Sync with User account
            if ($workEmail) {
                $targetUser = User::where('email', $workEmail)->first();

                if ($targetUser) {
                    // A user record with this email already exists (e.g. created via Google SSO)
                    $oldUserId = $employee->user_id;

                    // Link employee to the existing user record
                    $employee->update(['user_id' => $targetUser->id]);

                    // Sync target user name & role
                    $targetUser->update(['name' => $employee->name]);
                    if (!$targetUser->hasRole('admin') && !$targetUser->hasRole('employee')) {
                        $targetUser->assignRole('employee');
                    }

                    // If employee previously had a different placeholder user, clean it up safely
                    if ($oldUserId && $oldUserId !== $targetUser->id) {
                        try {
                            $oldUser = User::find($oldUserId);
                            if ($oldUser && !$oldUser->hasRole('admin') && Employee::where('user_id', $oldUser->id)->count() === 0) {
                                $oldUser->delete();
                            }
                        } catch (\Throwable $e) {}
                    }
                } else {
                    // No user with this email exists yet
                    if ($employee->user) {
                        $employee->user->update([
                            'email' => $workEmail,
                            'name' => $employee->name,
                        ]);
                        if (!$employee->user->hasRole('employee')) {
                            $employee->user->assignRole('employee');
                        }
                    } else {
                        $newUser = User::create([
                            'name' => $employee->name,
                            'email' => $workEmail,
                            'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(32)),
                        ]);
                        $newUser->assignRole('employee');
                        $employee->update(['user_id' => $newUser->id]);
                    }
                }
            }

            // Synchronize administrative role based on approval body (HR Officer / Executive Office)
            $employee->syncUserAdminRole();

            $employee->address()->updateOrCreate(['employee_id' => $employee->id], [
                'region' => $request->region,
                'province' => $request->province,
                'municipality_city' => $request->municipality_city,
                'barangay' => $request->barangay,
                'permanent_address' => $request->permanent_address,
                'current_address' => $request->current_address,
                'zip_code' => $request->zip_code,
            ]);

            $employee->governmentIds()->updateOrCreate(['employee_id' => $employee->id], [
                'sss_no' => $request->sss_no,
                'pagibig_no' => $request->pagibig_no,
                'philhealth_no' => $request->philhealth_no,
                'tin_no' => $request->tin_no
            ]);

            $employee->compensation()->updateOrCreate(['employee_id' => $employee->id], [
                'basic_salary_80' => $request->basic_salary_80 ?? 0,
                'allowance_20' => $request->allowance_20 ?? 0,
                'total_compensation' => $request->total_compensation ?? 0,
                'sss_deduction' => $request->sss_deduction ?? 0,
                'ph_deduction' => $request->ph_deduction ?? 0,
                'pi_deduction' => $request->pi_deduction ?? 0
            ]);

            $employee->education()->updateOrCreate(['employee_id' => $employee->id], [
                'primary_school' => $request->primary_school,
                'secondary_school' => $request->secondary_school
            ]);

            $employee->iq()->updateOrCreate(['employee_id' => $employee->id], [
                'total_stanine' => $request->total_stanine,
                'verbal' => $request->verbal,
                'non_verbal' => $request->non_verbal,
                'holland_personality' => $request->holland_personality
            ]);

            $employee->lastEmployment()->updateOrCreate(['employee_id' => $employee->id], [
                'company' => $request->prev_company,
                'position' => $request->prev_position,
                'last_salary' => $request->last_salary,
                'reason_for_leaving' => $request->reason_for_leaving
            ]);

            $employee->family()->updateOrCreate(['employee_id' => $employee->id], [
                'marriage_type' => $request->marriage_type,
                'spouse_occupation' => $request->spouse_occupation,
                'no_of_siblings' => $request->no_of_siblings ?? 0,
                'no_of_children' => $request->no_of_children ?? 0,
                'dependents' => $request->dependents,
                'single_status' => $request->single_status
            ]);

            DB::commit();

            // Notify employee if leave balances or compensation were adjusted
            $notifService = app(\App\Services\NotificationService::class);
            $newVl = (float)($employee->fresh()->vacation_leave_credits ?? 15.0);
            $newSl = (float)($employee->fresh()->sick_leave_credits ?? 15.0);
            $newEl = (float)($employee->fresh()->emergency_leave_credits ?? 3.0);
            $newComp = (float)($employee->fresh()->compensation?->total_compensation ?? 0.0);

            if (round($newVl, 2) !== round($oldVl, 2)) {
                $notifService->notifyLeaveCreditAdjustment($employee, 'vacation', $oldVl, $newVl);
            }
            if (round($newSl, 2) !== round($oldSl, 2)) {
                $notifService->notifyLeaveCreditAdjustment($employee, 'sick', $oldSl, $newSl);
            }
            if (round($newEl, 2) !== round($oldEl, 2)) {
                $notifService->notifyLeaveCreditAdjustment($employee, 'emergency', $oldEl, $newEl);
            }
            if (round($newComp, 2) !== round($oldCompensation, 2)) {
                $notifService->notifyCompensationUpdated($employee, $oldCompensation, $newComp);
            }

            return redirect()->route('admin.employees.profile', $employee)->with('success', 'Employee updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'separation_date' => 'nullable|date',
            'separation_reason' => 'nullable|string|max:255',
            'separation_notes' => 'nullable|string|max:1000',
        ]);

        $employee->update([
            'is_active' => false,
            'separation_date' => $validated['separation_date'] ?? now()->toDateString(),
            'separation_reason' => $validated['separation_reason'] ?? 'Resigned',
            'separation_notes' => $validated['separation_notes'] ?? null,
        ]);

        // Retain 100% of information in database. Do NOT call $employee->delete()!
        return redirect()->route('admin.employees.index', ['view' => 'inactive'])
            ->with('success', "Employee {$employee->name} has been marked as resigned/inactive. All historical records, payslips, and 201 files have been permanently preserved in the archives.");
    }

    public function reactivate(Employee $employee)
    {
        $employee->update([
            'is_active' => true,
            'separation_date' => null,
            'separation_reason' => null,
            'separation_notes' => null,
        ]);

        return redirect()->route('admin.employees.profile', $employee)
            ->with('success', "Employee {$employee->name} has been successfully reactivated.");
    }

    public function import(Request $request)
    {
        $file = $this->importService->validateSpreadsheet($request);
        $import = $this->importService->importEmployees($file);
        $msg = "Imported {$import->imported} employees successfully.";
        if ($import->errors) $msg .= ' Some rows had errors: ' . implode('; ', array_slice($import->errors, 0, 3));
        return back()->with('success', $msg);
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'xlsx');
        $fileName = 'LASAC_Employees_Full_Report_' . date('Y-m-d_His') . '.' . $format;
        return Excel::download(new \App\Exports\EmployeesExport(), $fileName);
    }
}
