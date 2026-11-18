<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HealthAssistanceFund extends Model
{
    use HasFactory;

    protected $table = 'health_assistance_funds';

    protected $fillable = [
        'employee_id',
        'member_number',
        'plan_year',
        'contribution_amount',
        'coverage_amount',
        'amount_used',
        'remaining_balance',
        'start_date',
        'end_date',
        'validity_days',
        'payment_method',
        'receipt_no',
        'receipt_file_path',
        'status',
        'expiration_notified_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'expiration_notified_at' => 'datetime',
        'contribution_amount' => 'decimal:2',
        'coverage_amount' => 'decimal:2',
        'amount_used' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'validity_days' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(HealthAssistanceClaim::class, 'health_assistance_fund_id')
            ->orderBy('claim_date', 'desc')
            ->orderBy('id', 'desc');
    }

    /**
     * Number of remaining days until coverage expires.
     */
    public function getDaysRemainingAttribute(): int
    {
        if (!$this->end_date) {
            return 0;
        }

        $today = now('Asia/Manila')->startOfDay();
        $end = $this->end_date->copy()->startOfDay();

        if ($today->gt($end)) {
            return 0;
        }

        return (int) $today->diffInDays($end, false);
    }

    /**
     * Dynamically computed status reflecting real-time expiration date.
     */
    public function getComputedStatusAttribute(): string
    {
        if ($this->status === 'cancelled') {
            return 'cancelled';
        }

        if (!$this->end_date) {
            return $this->status ?: 'active';
        }

        $today = now('Asia/Manila')->startOfDay();
        $end = $this->end_date->copy()->startOfDay();

        if ($today->gt($end)) {
            return 'expired';
        }

        $days = (int) $today->diffInDays($end, false);
        if ($days <= 30) {
            return 'expiring_soon';
        }

        return 'active';
    }

    /**
     * True if currently within 30 days before expiration.
     */
    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->computed_status === 'expiring_soon';
    }

    /**
     * True if past the expiration date.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->computed_status === 'expired';
    }

    /**
     * True if currently valid and usable.
     */
    public function getIsValidAttribute(): bool
    {
        return in_array($this->computed_status, ['active', 'expiring_soon']);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->computed_status) {
            'active' => 'Active Coverage',
            'expiring_soon' => 'Expiring Soon (' . $this->days_remaining . ' ' . ($this->days_remaining === 1 ? 'day' : 'days') . ' left)',
            'expired' => 'Expired (' . ($this->end_date ? $this->end_date->format('M d, Y') : 'Past') . ')',
            'cancelled' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $this->computed_status)),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->computed_status) {
            'active' => 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800/60',
            'expiring_soon' => 'bg-amber-100 text-amber-900 border-amber-300 dark:bg-amber-950/80 dark:text-amber-300 dark:border-amber-800',
            'expired' => 'bg-slate-100 text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
            'cancelled' => 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800/60',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    /**
     * Progress percentage (0 - 100) of 365 days elapsed.
     */
    public function getValidityProgressPercentageAttribute(): float
    {
        if (!$this->start_date || !$this->end_date) {
            return 100;
        }

        $totalDays = max(1, $this->validity_days ?: 365);
        $daysElapsed = max(0, (int) $this->start_date->diffInDays(now('Asia/Manila')->startOfDay(), false));

        if ($daysElapsed >= $totalDays) {
            return 100.0;
        }

        return round(($daysElapsed / $totalDays) * 100, 1);
    }

    /**
     * Percentage of health assistance coverage amount consumed.
     */
    public function getCoverageUtilizationPercentageAttribute(): float
    {
        if ($this->coverage_amount <= 0) {
            return 0.0;
        }

        $pct = ($this->amount_used / $this->coverage_amount) * 100;
        return round(min(100.0, max(0.0, $pct)), 1);
    }

    /**
     * Recompute amount used and remaining balance from approved claims.
     */
    public function recomputeBalance(): void
    {
        $approvedSum = (float) $this->claims()->where('status', 'approved')->sum('amount_approved');
        $this->amount_used = $approvedSum;
        $this->remaining_balance = max(0, (float) $this->coverage_amount - $approvedSum);
        $this->save();
    }
}
