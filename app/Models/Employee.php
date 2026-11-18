<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model {
    use SoftDeletes;

    protected $fillable = [
        'user_id','employee_no','name','email','position','salary_grade',
        'employment_status','department_id','date_hired','birthdate',
        'sex','civil_status','photo','is_active',
        'separation_date','separation_reason','separation_notes',
        'approval_role','vacation_leave_credits','sick_leave_credits','emergency_leave_credits',
    ];

    protected $casts = [
        'date_hired' => 'date',
        'birthdate' => 'date',
        'is_active' => 'boolean',
        'separation_date' => 'date',
        'vacation_leave_credits' => 'decimal:1',
        'sick_leave_credits' => 'decimal:1',
        'emergency_leave_credits' => 'decimal:1',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function getIsResignedAttribute(): bool
    {
        return !$this->is_active;
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->is_active) {
            return 'Active';
        }
        return $this->separation_reason ?: 'Resigned / Inactive';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        if ($this->is_active) {
            return 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800/60';
        }
        return 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800/60';
    }

    public function getAgeAttribute(): ?int {
        return $this->birthdate ? $this->birthdate->age : null;
    }

    public function getYearsOfServiceAttribute(): ?float {
        return $this->date_hired ? round($this->date_hired->diffInMonths(now()) / 12, 1) : null;
    }


    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function address(): HasOne { return $this->hasOne(EmployeeAddress::class); }
    public function governmentIds(): HasOne { return $this->hasOne(EmployeeGovernmentId::class); }
    public function compensation(): HasOne { return $this->hasOne(EmployeeCompensation::class); }
    public function education(): HasOne { return $this->hasOne(EmployeeEducation::class); }
    public function iq(): HasOne { return $this->hasOne(EmployeeIq::class); }
    public function lastEmployment(): HasOne { return $this->hasOne(EmployeeLastEmployment::class); }
    public function family(): HasOne { return $this->hasOne(EmployeeFamily::class); }
    public function certificates(): HasMany { return $this->hasMany(CertificateTraining::class); }
    public function attendanceRecords(): HasMany { return $this->hasMany(AttendanceRecord::class); }
    public function timeCreditLedger(): HasMany { return $this->hasMany(TimeCreditLedger::class); }
    public function payslips(): HasMany { return $this->hasMany(Payslip::class); }
    public function leaveRequests(): HasMany { return $this->hasMany(LeaveRequest::class); }
    public function loans(): HasMany { return $this->hasMany(EmployeeLoan::class); }
    public function activeLoans(): HasMany { return $this->hasMany(EmployeeLoan::class)->where('status', 'active'); }
    public function staffRequests(): HasMany { return $this->hasMany(StaffRequest::class); }
    public function disciplinaryNotices(): HasMany { return $this->hasMany(DisciplinaryNotice::class); }
    public function healthAssistanceFunds(): HasMany { return $this->hasMany(HealthAssistanceFund::class)->orderBy('start_date', 'desc')->orderBy('id', 'desc'); }
    public function latestHealthAssistance(): HasOne { return $this->hasOne(HealthAssistanceFund::class)->latestOfMany('start_date'); }

    /**
     * Check if employee has any overdue Notice to Explain (NTE) that blocks time-in.
     */
    public function hasOverdueNte(?string $date = null): bool
    {
        try {
            $targetDate = $date ? \Carbon\Carbon::parse($date)->startOfDay() : now('Asia/Manila')->startOfDay();
            return $this->disciplinaryNotices()
                ->where('requires_explanation', true)
                ->whereNull('scanned_nte_path')
                ->whereNull('nte_received_at')
                ->whereNotIn('status', ['resolved', 'closed'])
                ->where('explanation_deadline', '<', $targetDate->toDateString())
                ->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Check if employee's time-in is currently blocked by an overdue NTE.
     */
    public function getIsTimeInBlockedAttribute(): bool
    {
        return $this->hasOverdueNte();
    }

    public function getTotalLoanBalanceAttribute(): float {
        try {
            return (float) $this->activeLoans()->sum('remaining_balance');
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    public function getTimeCreditBalanceAttribute(): float {
        try {
            return (float) $this->timeCreditLedger()->where('status', 'approved')->sum('hours');
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    public function getPendingTimeCreditsAttribute(): float {
        try {
            return (float) $this->timeCreditLedger()->where('status', 'pending')->sum('hours');
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    public function getUsedVacationLeaveAttribute(): float {
        try {
            return (float) $this->leaveRequests()
                ->where('leave_type', 'vacation')
                ->where('status', 'approved')
                ->whereYear('start_date', now()->year)
                ->sum('days_count');
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    public function getRemainingVacationLeaveAttribute(): float {
        $credits = $this->vacation_leave_credits ?? 15.0;
        return max(0, round($credits - $this->used_vacation_leave, 1));
    }

    public function getUsedSickLeaveAttribute(): float {
        try {
            return (float) $this->leaveRequests()
                ->where('leave_type', 'sick')
                ->where('status', 'approved')
                ->whereYear('start_date', now()->year)
                ->sum('days_count');
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    public function getRemainingSickLeaveAttribute(): float {
        $credits = $this->sick_leave_credits ?? 15.0;
        return max(0, round($credits - $this->used_sick_leave, 1));
    }

    public function getUsedEmergencyLeaveAttribute(): float {
        try {
            return (float) $this->leaveRequests()
                ->where('leave_type', 'emergency')
                ->where('status', 'approved')
                ->whereYear('start_date', now()->year)
                ->sum('days_count');
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    public function getRemainingEmergencyLeaveAttribute(): float {
        $credits = $this->emergency_leave_credits ?? 3.0;
        return max(0, round($credits - $this->used_emergency_leave, 1));
    }

    public function getApprovalRoleLabelAttribute(): string {
        return match($this->approval_role) {
            'section_head' => 'Section Head',
            'hr_officer' => 'HR Officer',
            'it_officer' => 'IT Administrator',
            'executive_office' => 'Executive Office',
            'executive_director' => 'Executive Director',
            default => 'Staff / Standard Member',
        };
    }

    /**
     * Organizational hierarchy rank (lower number = higher organizational rank):
     * 0: Section Head (Always 1st within department)
     * 1: Top leadership / Executive Office / Directors / Head / Chiefs
     * 2: Managers / Supervisors / Coordinators / Officers / Administrators
     * 3: Specialists / Technical Leads / Senior Staff / Accountants / Engineers
     * 4: Assistants / Associates / Bookkeepers / Cashiers / Juniors / Technicians
     * 5: Staff / Clerks / Organizers / Utility / Drivers / General Services
     */
    public function getHierarchyRankAttribute(): int
    {
        if ($this->approval_role === 'section_head') {
            return 0;
        }

        if (in_array($this->approval_role, ['executive_office', 'executive_director'])) {
            return 1;
        }

        $pos = strtolower(trim($this->position ?? ''));

        // Rank 1: Top leadership / Directors / Head / Chiefs
        if (preg_match('/\b(executive|director|head|chief|president|oic|officer-in-charge)\b/i', $pos)) {
            return 1;
        }

        // Rank 2: Managers / Supervisors / Coordinators / Officers
        if (preg_match('/\b(manager|supervisor|coordinator|lead|officer|administrator)\b/i', $pos)) {
            return 2;
        }

        // Rank 3: Specialists / Accountants / Engineers / Senior
        if (preg_match('/\b(specialist|accountant|analyst|engineer|senior|auditor)\b/i', $pos)) {
            return 3;
        }

        // Rank 4: Assistants / Associates / Bookkeepers / Cashiers / Juniors
        if (preg_match('/\b(assistant|associate|bookkeeper|cashier|technician|junior)\b/i', $pos)) {
            return 4;
        }

        // Rank 5: Operational staff / Drivers / Utility / Helpers / Clerks
        return 5;
    }

    /**
     * Extracts numeric level from salary grade string (e.g. 'SG 18' -> 18).
     */
    public function getSalaryGradeLevelAttribute(): int
    {
        if (!$this->salary_grade) {
            return 0;
        }
        $digits = preg_replace('/[^0-9]/', '', (string) $this->salary_grade);
        return !empty($digits) ? (int) $digits : 0;
    }

    protected static function booted(): void
    {
        static::saved(function (Employee $employee) {
            $employee->syncUserAdminRole();
        });
    }

    /**
     * Synchronizes administrative roles on the linked User account based on approval authority.
     */
    public function syncUserAdminRole(): void
    {
        $user = $this->user ?: ($this->email ? User::where('email', $this->email)->first() : null);

        if (!$user) {
            return;
        }

        // Ensure roles exist in permission system
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'employee']);

        // Every employee user account must have the 'employee' role for portal access
        if (!$user->hasRole('employee')) {
            $user->assignRole('employee');
        }

        $adminEmails = config('hris.admin_emails', []);
        $isConfiguredAdmin = in_array(strtolower($user->email), $adminEmails);
        $isItDepartment = $this->department && (
            strtoupper($this->department->code ?? '') === 'IT' ||
            stripos($this->department->name ?? '', 'Information Technology') !== false
        );

        if ($isConfiguredAdmin || $isItDepartment || in_array($this->approval_role, ['hr_officer', 'executive_office', 'executive_director', 'it_officer'])) {
            if (!$user->hasRole('admin')) {
                $user->assignRole('admin');
            }
        } else {
            // Revoke admin if approval role is no longer administrative,
            // unless the user is a configured admin email.
            if ($user->hasRole('admin') && !$isConfiguredAdmin) {
                $hasOtherAdminRole = Employee::where('user_id', $user->id)
                    ->where('id', '!=', $this->id)
                    ->whereIn('approval_role', ['hr_officer', 'executive_office', 'executive_director', 'it_officer'])
                    ->exists();

                if (!$hasOtherAdminRole) {
                    $user->removeRole('admin');
                }
            }
        }
    }
}
