<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AttendanceRecord extends Model {
    protected $table = 'attendance_records';
    protected $fillable = [
        'employee_id',
        'date',
        'time_in',
        'time_out',
        'hours_worked',
        'late_minutes',
        'undertime_minutes',
        'overtime_minutes',
        'overtime_status',
        'overtime_approved_by',
        'overtime_approved_at',
        'status',
        'source',
    ];

    protected $casts = [
        'date' => 'date',
        'overtime_approved_at' => 'datetime',
    ];

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function overtimeApprover(): BelongsTo { return $this->belongsTo(User::class, 'overtime_approved_by'); }
    public function timeCreditEntry(): HasOne { return $this->hasOne(TimeCreditLedger::class, 'attendance_record_id'); }
}
