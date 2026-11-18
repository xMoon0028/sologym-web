<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffRequest extends Model
{
    use HasFactory;

    protected $table = 'staff_requests';

    protected $fillable = [
        'employee_id',
        'type',
        'status',
        'current_stage',
        'document_type',
        'purpose',
        'date_needed',
        'document_file_path',
        'seminar_title',
        'organizer',
        'venue',
        'start_date',
        'end_date',
        'estimated_budget',
        'invitation_file_path',
        'justification',
        'report_submitted_at',
        'report_summary',
        'report_action_plan',
        'certificate_file_path',
        'additional_materials_path',
        'report_status',
        'report_acknowledged_by',
        'report_acknowledged_at',
        'reviewed_by',
        'reviewed_at',
        'approved_at',
        'admin_notes',
        'rejection_reason',
    ];

    protected $casts = [
        'date_needed' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'estimated_budget' => 'decimal:2',
        'report_submitted_at' => 'datetime',
        'report_acknowledged_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function reportAcknowledger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'report_acknowledged_by');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(StaffRequestApproval::class)->orderBy('created_at', 'asc');
    }

    public function getIsSeminarAttribute(): bool
    {
        return $this->type === 'seminar';
    }

    public function getIsDocumentAttribute(): bool
    {
        return $this->type === 'document';
    }

    public function getIsPendingAttribute(): bool
    {
        return in_array($this->status, [
            'pending',
            'pending_section_head',
            'pending_executive',
            'pending_executive_director',
        ]);
    }

    /**
     * Check if this request needs a seminar report submitted by the employee.
     */
    public function getRequiresSeminarReportAttribute(): bool
    {
        return $this->is_seminar && in_array($this->status, ['approved', 'report_pending']) && empty($this->report_submitted_at);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending Review',
            'pending_section_head' => 'Pending Section Head',
            'pending_executive' => 'Pending Executive Office',
            'pending_executive_director' => 'Pending Executive Director',
            'approved' => $this->is_seminar ? 'Approved (Report Pending)' : 'Approved / Issued',
            'report_pending' => 'Seminar Report Pending',
            'completed' => $this->is_seminar ? 'Completed (Report Acknowledged)' : 'Completed',
            'rejected' => 'Disapproved',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending', 'pending_section_head' => 'bg-amber-50 text-amber-800 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800/80',
            'pending_executive', 'pending_executive_director' => 'bg-purple-50 text-purple-800 border-purple-300 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800/80',
            'approved' => 'bg-blue-50 text-blue-800 border-blue-300 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800/80',
            'report_pending' => 'bg-indigo-50 text-indigo-800 border-indigo-300 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800/80',
            'completed' => 'bg-emerald-50 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800/80',
            'rejected' => 'bg-rose-50 text-rose-800 border-rose-300 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800/80',
            default => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
        };
    }

    public function getStageLabelAttribute(): string
    {
        return match ($this->status) {
            'pending_section_head' => 'Section Head Endorsement',
            'pending_executive' => 'Executive Office Final Approval',
            'pending_executive_director' => 'Executive Director Final Approval',
            'approved' => 'Approved (Awaiting Seminar / Report)',
            'report_pending' => 'Seminar Report Pending',
            'completed' => 'Completed & Acknowledged',
            'rejected' => 'Disapproved',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getSectionHeadApprovalAttribute(): ?StaffRequestApproval
    {
        return $this->approvals->where('stage', 'section_head')->last();
    }

    public function getExecutiveApprovalAttribute(): ?StaffRequestApproval
    {
        return $this->approvals->whereIn('stage', ['executive_office', 'executive_director'])->last();
    }
}
