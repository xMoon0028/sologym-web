<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffRequestApproval extends Model
{
    protected $fillable = [
        'staff_request_id',
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
        'formatted_action_at',
    ];

    public function getFormattedActionAtAttribute(): string
    {
        return $this->created_at ? $this->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') : '';
    }

    public function staffRequest(): BelongsTo
    {
        return $this->belongsTo(StaffRequest::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function getStageLabelAttribute(): string
    {
        return match ($this->stage) {
            'submission' => 'Request Submission',
            'section_head' => 'Section Head Endorsement / Approval',
            'executive_office' => 'Executive Office Final Approval',
            'executive_director' => 'Executive Director Final Approval',
            'hr' => 'Human Resources Review',
            default => ucfirst(str_replace('_', ' ', $this->stage)),
        };
    }

    public function getActionBadgeClassAttribute(): string
    {
        return match ($this->action) {
            'approved' => 'bg-emerald-50 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
            'recommended' => 'bg-blue-50 text-blue-800 border-blue-300 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800',
            'submitted' => 'bg-slate-100 text-slate-800 border-slate-300 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
            'rejected' => 'bg-rose-50 text-rose-800 border-rose-300 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800',
            default => 'bg-slate-100 text-slate-700 border-slate-300',
        };
    }
}
