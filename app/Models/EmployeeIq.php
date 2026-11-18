<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeIq extends Model {
    protected $table = 'employee_iq';
    protected $fillable = ['employee_id','total_stanine','verbal','non_verbal','holland_personality'];
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
}
