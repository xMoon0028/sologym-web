<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLastEmployment extends Model {
    protected $table = 'employee_last_employment';
    protected $fillable = ['employee_id','company','position','last_salary','reason_for_leaving'];
    protected $casts = ['last_salary' => 'decimal:2'];
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
}
