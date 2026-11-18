<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAddress extends Model {
    protected $fillable = [
        'employee_id',
        'region',
        'province',
        'municipality_city',
        'barangay',
        'permanent_address',
        'current_address',
        'zip_code',
    ];
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
}
