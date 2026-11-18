<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayslipCustomItem extends Model {
    protected $table = 'payslip_custom_items';
    protected $fillable = ['payslip_id','label','type','amount'];
    protected $casts = ['amount' => 'decimal:2'];
    public function payslip(): BelongsTo { return $this->belongsTo(Payslip::class); }
}
