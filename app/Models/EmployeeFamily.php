<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeFamily extends Model {
    protected $table = 'employee_family';
    protected $fillable = ['employee_id','marriage_type','spouse_occupation','no_of_siblings','no_of_children','dependents','single_status'];
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
}
