<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeCreditLedger extends Model {
    protected $table = 'time_credit_ledger';
    protected $fillable = [
        'employee_id',
        'date',
        'type',
        'status',
        'hours',
        'reason',
        'running_balance',
        'attendance_record_id',
        'created_by',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'date' => 'date',
        'hours' => 'decimal:2',
        'running_balance' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function attendanceRecord(): BelongsTo { return $this->belongsTo(AttendanceRecord::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }

    public function scopePending($query) { return $query->where('status', 'pending'); }
    public function scopeApproved($query) { return $query->where('status', 'approved'); }
}
