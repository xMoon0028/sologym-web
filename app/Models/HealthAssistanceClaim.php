<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthAssistanceClaim extends Model
{
    use HasFactory;

    protected $table = 'health_assistance_claims';

    protected $fillable = [
        'health_assistance_fund_id',
        'claim_no',
        'claim_date',
        'claim_type',
        'healthcare_provider',
        'diagnosis_reason',
        'amount_requested',
        'amount_approved',
        'status',
        'receipt_file_path',
        'processed_by',
        'remarks',
    ];

    protected $casts = [
        'claim_date' => 'date',
        'amount_requested' => 'decimal:2',
        'amount_approved' => 'decimal:2',
    ];

    public function fund(): BelongsTo
    {
        return $this->belongsTo(HealthAssistanceFund::class, 'health_assistance_fund_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getClaimTypeLabelAttribute(): string
    {
        return match ($this->claim_type) {
            'hospitalization' => 'Hospitalization / Inpatient',
            'outpatient' => 'Outpatient Consultation',
            'laboratory' => 'Laboratory & Diagnostic Procedures',
            'medicine' => 'Prescription Medicine Reimbursement',
            'dental' => 'Dental Care',
            'emergency' => 'Emergency Room Care',
            default => ucfirst(str_replace('_', ' ', $this->claim_type)),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'Approved & Disbursed',
            'pending' => 'Pending Review',
            'disapproved', 'rejected' => 'Disapproved',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800/60',
            'pending' => 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800/60',
            'disapproved', 'rejected' => 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800/60',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }
}
