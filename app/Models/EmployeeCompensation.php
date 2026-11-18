<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeCompensation extends Model {
    protected $table = 'employee_compensation';
    protected $fillable = ['employee_id','basic_salary_80','allowance_20','total_compensation','sss_deduction','ph_deduction','pi_deduction'];
    protected $casts = ['basic_salary_80'=>'decimal:2','allowance_20'=>'decimal:2','total_compensation'=>'decimal:2','sss_deduction'=>'decimal:2','ph_deduction'=>'decimal:2','pi_deduction'=>'decimal:2'];
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }

    public function getPerDayRateAttribute(): float {
        return $this->total_compensation > 0 ? round(($this->total_compensation * 12) / 261, 4) : 0;
    }
    public function getPerHourRateAttribute(): float {
        return round($this->per_day_rate / 8, 4);
    }
    public function getPerMinuteRateAttribute(): float {
        return round($this->per_hour_rate / 60, 4);
    }
    public function getSemiMonthlyGrossAttribute(): float {
        return round($this->total_compensation / 2, 2);
    }
}
