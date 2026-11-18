<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateTraining extends Model {
    protected $table = 'certificates_trainings';
    protected $fillable = ['employee_id','type','title','issuer','date_issued','expiry_date','file_path','notes'];
    protected $casts = ['date_issued' => 'date', 'expiry_date' => 'date'];

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }

    public function getIsExpiringAttribute(): bool {
        return $this->expiry_date && $this->expiry_date->diffInDays(now()) <= 30 && $this->expiry_date->isFuture();
    }
    public function getIsExpiredAttribute(): bool {
        return $this->expiry_date && $this->expiry_date->isPast();
    }
}
