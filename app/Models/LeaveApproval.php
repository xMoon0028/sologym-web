<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveApproval extends Model
{
    protected $fillable = [
        'leave_request_id',
        'stage',
        'action',
        'approver_id',
        'approver_name',
        'approver_role',
        'notes',
    ];

    protected $appends = [
        'stage_label',
        'action_badge_class',
        'formatted_approved_at',
    ];

    public function getFormattedApprovedAtAttribute(): string
    {
        return $this->created_at ? $this->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') : '';
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function getStageLabelAttribute(): string
    {
        return match ($this->stage) {
            'section_head' => 'Section Head Approval',
            'hr' => 'HR Recommending Approval',
            'executive_office' => 'Executive Office Final Approval',
            'executive_director' => 'Executive Director Final Approval',
            default => ucfirst(str_replace('_', ' ', $this->stage)),
        };
    }

    public function getActionBadgeClassAttribute(): string
    {
        return match ($this->action) {
            'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'recommended' => 'bg-blue-50 text-blue-700 border-blue-200',
            'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
            'expired' => 'bg-orange-50 text-orange-800 border-orange-200',
            'returned' => 'bg-amber-50 text-amber-700 border-amber-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }
}
