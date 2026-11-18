<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeEducation extends Model {
    protected $table = 'employee_education';
    protected $fillable = ['employee_id','primary_school','secondary_school'];
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
}
