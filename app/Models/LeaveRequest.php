<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveRequest extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'days_count',
        'is_half_day',
        'half_day_type',
        'reason',
        'attachment',
        'status',
        'current_stage',
        'stage_expires_at',
        'expired_at',
        'rejection_reason',
        'rejected_by',
        'rejected_at',
        'approved_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days_count' => 'decimal:1',
        'is_half_day' => 'boolean',
        'stage_expires_at' => 'datetime',
        'expired_at' => 'datetime',
        'rejected_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(LeaveApproval::class)->orderBy('created_at', 'asc');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function getLeaveTypeLabelAttribute(): string
    {
        return match ($this->leave_type) {
            'vacation' => 'Vacation Leave (VL)',
            'sick' => 'Sick Leave (SL)',
            'emergency' => 'Emergency Leave (EL)',
            'maternity' => 'Maternity Leave (ML)',
            'paternity' => 'Paternity Leave (PL)',
            'solo_parent', 'parental' => 'Parental Leave / Solo Parent Leave (SPL)',
            'vawc', 'bwl' => 'Victims of Violence Against Women & Children / Battered Women Leave (BWL)',
            'gynecological', 'special_women', 'gl' => 'Gynecological Leaves for Women (GL)',
            'time_off', 'offset' => 'Time Off Leave (TOL)',
            'bereavement' => 'Bereavement Leave (BL)',
            'unpaid' => 'Leave Without Pay (LWOP)',
            default => ucfirst(str_replace('_', ' ', $this->leave_type)),
        };
    }

    public function getLeaveTypeCode(): string
    {
        return match ($this->leave_type) {
            'vacation' => 'VL',
            'sick' => 'SL',
            'emergency' => 'EL',
            'maternity' => 'ML',
            'paternity' => 'PL',
            'solo_parent', 'parental' => 'SPL',
            'vawc', 'bwl' => 'BWL',
            'gynecological', 'special_women', 'gl' => 'GL',
            'time_off', 'offset' => 'TOL',
            'bereavement' => 'BL',
            'unpaid' => 'LWOP',
            default => strtoupper(substr((string) $this->leave_type, 0, 3)),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending_section_head' => 'Pending Section Head',
            'pending_hr' => 'Pending HR Review',
            'pending_executive' => 'Pending Executive Office',
            'pending_executive_director' => 'Pending Executive Director',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'expired' => 'Expired (1-Day Limit Exceeded)',
            'cancelled' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending_section_head' => 'bg-amber-50 text-amber-800 border-amber-200',
            'pending_hr' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'pending_executive' => 'bg-purple-50 text-purple-700 border-purple-200',
            'pending_executive_director' => 'bg-fuchsia-50 text-fuchsia-700 border-fuchsia-200',
            'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
            'expired' => 'bg-orange-50 text-orange-800 border-orange-200',
            'cancelled' => 'bg-slate-100 text-slate-600 border-slate-200',
            default => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }

    protected $appends = [
        'leave_type_label',
        'status_label',
        'status_badge_class',
        'stage_number',
        'badge_class',
        'type_label',
        'is_pending',
        'is_expired',
        'stage',
        'stage_label',
    ];

    public function getIsPendingAttribute(): bool
    {
        return in_array($this->status, [
            'pending_section_head',
            'pending_hr',
            'pending_executive',
            'pending_executive_director',
        ]);
    }

    public function getIsExpiredAttribute(): bool
    {
        if ($this->status === 'expired') {
            return true;
        }
        return $this->is_pending && $this->stage_expires_at && $this->stage_expires_at->isPast();
    }

    public function getStageNumberAttribute(): int
    {
        return match ($this->status) {
            'pending_section_head' => 1,
            'pending_executive', 'pending_executive_director', 'pending_hr' => 2,
            'approved' => 3,
            default => 0,
        };
    }

    public function getBadgeClassAttribute(): string
    {
        return $this->status_badge_class;
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->leave_type_label;
    }

    public function getStageAttribute(): string
    {
        return $this->current_stage ?? 'section_head';
    }

    public function getStageLabelAttribute(): string
    {
        return match ($this->current_stage) {
            'section_head' => 'Section Head Approval (1-Day Limit)',
            'hr' => 'HR Information Copy',
            'executive_office', 'executive' => 'Executive Office Approval (1-Day Limit)',
            'executive_director' => 'Executive Director Approval (1-Day Limit)',
            'completed' => 'Completed',
            default => ucfirst(str_replace('_', ' ', $this->current_stage ?? '')),
        };
    }
}
