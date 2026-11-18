<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPeriod extends Model {
    protected $table = 'payroll_periods';
    protected $fillable = [
        'name', 'start_date', 'end_date', 'date_of_payment', 'cutoff_type',
        'status', 'created_by', 'finalized_by', 'finalized_at', 'released_at'
    ];
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'date_of_payment' => 'date',
        'finalized_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function payslips(): HasMany { return $this->hasMany(Payslip::class, 'period_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function finalizer(): BelongsTo { return $this->belongsTo(User::class, 'finalized_by'); }

    public function getIsEditableAttribute(): bool {
        return in_array($this->status, ['draft', 'under_review']);
    }

    /**
     * Auto-detect cutoff type based on date range:
     * - Dates around 21 to 5 (crosses month boundary or start >= 21 or end <= 5) => mid_month
     * - Dates around 6 to 20 (within same month) => end_month
     */
    public static function detectCutoffType($startDate, $endDate): string {
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);

        if ($start->day > $end->day || $start->day >= 21 || $end->day <= 5) {
            return 'mid_month';
        }
        return 'end_month';
    }

    public function getIsMidMonthAttribute(): bool {
        return ($this->cutoff_type ?? static::detectCutoffType($this->start_date, $this->end_date)) === 'mid_month';
    }

    public function getIsEndMonthAttribute(): bool {
        return !$this->is_mid_month;
    }

    public function getCutoffLabelAttribute(): string {
        return $this->is_mid_month ? 'Mid-Month Salary (21st–5th)' : 'End-Month Salary (6th–20th)';
    }

    public function getCutoffDeductionsSummaryAttribute(): string {
        return $this->is_mid_month ? 'PhilHealth & HDMF Active (SSS: ₱0.00)' : 'SSS Active (PhilHealth & HDMF: ₱0.00)';
    }

    /**
     * Whether the payslips are currently visible and accessible to staff.
     */
    public function isVisibleToEmployees(): bool {
        if ($this->status !== 'released') {
            return false;
        }

        // If released_at is null or has passed, staff can see it.
        return !$this->released_at || $this->released_at->isPast();
    }

    /**
     * Whether the period has been scheduled for future release.
     */
    public function isScheduled(): bool {
        return $this->status === 'released' && $this->released_at && $this->released_at->isFuture();
    }

    /**
     * Scope for periods that employees are authorized to view.
     */
    public function scopeVisibleToEmployees($query) {
        return $query->where('status', 'released')
            ->where(function ($q) {
                $q->whereNull('released_at')
                  ->orWhere('released_at', '<=', now());
            });
    }
}
