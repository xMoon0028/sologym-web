<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payslip extends Model {
    protected $fillable = [
        'employee_id','period_id','per_day_rate','per_hour_rate','per_minute_rate',
        'salary_and_allowances','holiday_pay','other_earnings',
        'sss_deduction','ph_deduction','hdmf_deduction',
        'absent_days','absences_deduction',
        'late_minutes','late_deduction',
        'undertime_minutes','undertime_deduction',
        'loan_deduction','sss_loan_deduction','pagibig_loan_deduction','salary_loan_deduction','other_deductions',
        'official_business_days','leave_days',
        'gross_earnings','total_deductions','net_pay','is_finalized',
    ];
    protected $casts = [
        'per_day_rate'=>'decimal:4','per_hour_rate'=>'decimal:4','per_minute_rate'=>'decimal:4',
        'salary_and_allowances'=>'decimal:2','holiday_pay'=>'decimal:2','other_earnings'=>'decimal:2',
        'sss_deduction'=>'decimal:2','ph_deduction'=>'decimal:2','hdmf_deduction'=>'decimal:2',
        'absences_deduction'=>'decimal:2','late_deduction'=>'decimal:2','undertime_deduction'=>'decimal:2',
        'loan_deduction'=>'decimal:2','sss_loan_deduction'=>'decimal:2','pagibig_loan_deduction'=>'decimal:2','salary_loan_deduction'=>'decimal:2','other_deductions'=>'decimal:2',
        'official_business_days'=>'integer','leave_days'=>'integer',
        'gross_earnings'=>'decimal:2','total_deductions'=>'decimal:2','net_pay'=>'decimal:2',
        'is_finalized'=>'boolean',
    ];

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function period(): BelongsTo { return $this->belongsTo(PayrollPeriod::class, 'period_id'); }
    public function customItems(): HasMany { return $this->hasMany(PayslipCustomItem::class); }
    public function loanPayments(): HasMany { return $this->hasMany(LoanPayment::class, 'payslip_id'); }

    /**
     * Rate accessors always compute using the statutory 261-day factor:
     * Per Day = (Monthly Total * 12) / 261
     * Per Hour = Per Day / 8
     * Per Minute = Per Hour / 60
     */
    public function getPerDayRateAttribute($value): float {
        if ($this->employee && $this->employee->compensation && $this->employee->compensation->total_compensation > 0) {
            return $this->employee->compensation->per_day_rate;
        }
        if ($value !== null && (float)$value > 0) {
            return (float) $value;
        }
        if ($this->salary_and_allowances > 0) {
            return round(($this->salary_and_allowances * 2 * 12) / 261, 4);
        }
        return 0.0;
    }

    public function getPerHourRateAttribute($value): float {
        $perDay = $this->per_day_rate;
        if ($perDay > 0) {
            return round($perDay / 8, 4);
        }
        return (float) ($value ?? 0);
    }

    public function getPerMinuteRateAttribute($value): float {
        $perHour = $this->per_hour_rate;
        if ($perHour > 0) {
            return round($perHour / 60, 4);
        }
        return (float) ($value ?? 0);
    }

    /**
     * Synchronize and snapshot rates and statutory attendance deductions
     * according to the 261-day formula.
     */
    public function syncRatesAndDeductions(): void {
        $perDay = $this->per_day_rate;
        $perHour = $this->per_hour_rate;
        $perMinute = $this->per_minute_rate;

        $this->attributes['per_day_rate'] = $perDay;
        $this->attributes['per_hour_rate'] = $perHour;
        $this->attributes['per_minute_rate'] = $perMinute;

        if ($this->absent_days > 0) {
            $this->absences_deduction = round($this->absent_days * $perDay, 2);
        }
        if ($this->late_minutes > 0) {
            $this->late_deduction = round($this->late_minutes * $perMinute, 2);
        }
        if ($this->undertime_minutes > 0) {
            $this->undertime_deduction = round($this->undertime_minutes * $perMinute, 2);
        }

        $this->recompute();
    }

    public function recompute(): void {
        // Snapshot 261 rates into the model attributes
        $this->attributes['per_day_rate'] = $this->per_day_rate;
        $this->attributes['per_hour_rate'] = $this->per_hour_rate;
        $this->attributes['per_minute_rate'] = $this->per_minute_rate;

        $customEarnings = $this->customItems()->where('type','earning')->sum('amount');
        $customDeductions = $this->customItems()->where('type','deduction')->sum('amount');

        $brokenDownLoans = (float) $this->sss_loan_deduction + (float) $this->pagibig_loan_deduction + (float) $this->salary_loan_deduction;
        if ($brokenDownLoans > 0 || ($this->loan_deduction === null || (float) $this->loan_deduction == 0)) {
            $this->loan_deduction = $brokenDownLoans;
        }

        $this->gross_earnings = $this->salary_and_allowances + $this->holiday_pay + $this->other_earnings + $customEarnings;
        $this->total_deductions = $this->sss_deduction + $this->ph_deduction + $this->hdmf_deduction
            + $this->absences_deduction + $this->late_deduction + $this->undertime_deduction
            + $this->loan_deduction + $this->other_deductions + $customDeductions;
        $this->net_pay = $this->gross_earnings - $this->total_deductions;
        $this->save();
    }
}
