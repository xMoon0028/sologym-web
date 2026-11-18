<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeGovernmentId extends Model {
    protected $table = 'employee_government_ids';
    protected $fillable = ['employee_id','sss_no','pagibig_no','philhealth_no','tin_no'];
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
}
