<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeLoan extends Model
{
    protected $table = 'employee_loans';

    protected $fillable = [
        'employee_id',
        'loan_type',
        'loan_name',
        'reference_no',
        'principal_amount',
        'interest_amount',
        'total_amount',
        'monthly_amortization',
        'amortization_per_cutoff',
        'deduction_schedule',
        'amount_paid',
        'remaining_balance',
        'start_date',
        'end_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'monthly_amortization' => 'decimal:2',
        'amortization_per_cutoff' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class, 'employee_loan_id')->latest('payment_date');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Compute progress percentage of repayment.
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_amount <= 0) {
            return 100.0;
        }

        $pct = ($this->amount_paid / $this->total_amount) * 100;
        return round(min(100.0, max(0.0, $pct)), 1);
    }

    /**
     * Determine badge visual appearance based on loan type.
     */
    public function getTypeBadgeAttribute(): array
    {
        return match ($this->loan_type) {
            'sss' => [
                'label' => 'SSS Loan',
                'bg' => 'bg-blue-50 dark:bg-blue-950/60',
                'text' => 'text-blue-700 dark:text-blue-300',
                'border' => 'border-blue-200 dark:border-blue-800',
                'icon' => '🏛️',
            ],
            'pagibig' => [
                'label' => 'Pag-IBIG Loan',
                'bg' => 'bg-emerald-50 dark:bg-emerald-950/60',
                'text' => 'text-emerald-700 dark:text-emerald-300',
                'border' => 'border-emerald-200 dark:border-emerald-800',
                'icon' => '🏠',
            ],
            'salary' => [
                'label' => 'Salary Loan',
                'bg' => 'bg-amber-50 dark:bg-amber-950/60',
                'text' => 'text-amber-700 dark:text-amber-300',
                'border' => 'border-amber-200 dark:border-amber-800',
                'icon' => '💵',
            ],
            'emergency' => [
                'label' => 'Emergency Loan',
                'bg' => 'bg-rose-50 dark:bg-rose-950/60',
                'text' => 'text-rose-700 dark:text-rose-300',
                'border' => 'border-rose-200 dark:border-rose-800',
                'icon' => '🚨',
            ],
            default => [
                'label' => ucfirst($this->loan_type) . ' Loan',
                'bg' => 'bg-purple-50 dark:bg-purple-950/60',
                'text' => 'text-purple-700 dark:text-purple-300',
                'border' => 'border-purple-200 dark:border-purple-800',
                'icon' => '📋',
            ],
        };
    }

    /**
     * Determine status badge visual appearance.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'active' => [
                'label' => 'Active',
                'bg' => 'bg-emerald-100 dark:bg-emerald-950/60',
                'text' => 'text-emerald-800 dark:text-emerald-300',
                'border' => 'border-emerald-200 dark:border-emerald-800',
            ],
            'completed' => [
                'label' => 'Completed',
                'bg' => 'bg-slate-100 dark:bg-slate-800',
                'text' => 'text-slate-700 dark:text-slate-300',
                'border' => 'border-slate-200 dark:border-slate-700',
            ],
            'paused' => [
                'label' => 'Paused',
                'bg' => 'bg-amber-100 dark:bg-amber-950/60',
                'text' => 'text-amber-800 dark:text-amber-300',
                'border' => 'border-amber-200 dark:border-amber-800',
            ],
            'cancelled' => [
                'label' => 'Cancelled',
                'bg' => 'bg-red-100 dark:bg-red-950/60',
                'text' => 'text-red-800 dark:text-red-300',
                'border' => 'border-red-200 dark:border-red-800',
            ],
            default => [
                'label' => ucfirst($this->status),
                'bg' => 'bg-slate-100 dark:bg-slate-800',
                'text' => 'text-slate-700 dark:text-slate-300',
                'border' => 'border-slate-200 dark:border-slate-700',
            ],
        };
    }

    /**
     * Recompute amount_paid and remaining_balance from payment ledger.
     */
    public function recomputeBalances(): void
    {
        $totalPaid = (float) $this->payments()->sum('amount');
        $this->amount_paid = $totalPaid;
        $this->remaining_balance = max(0.0, (float) $this->total_amount - $totalPaid);

        if ($this->remaining_balance <= 0 && $this->status === 'active') {
            $this->status = 'completed';
        }

        $this->save();
    }
}
