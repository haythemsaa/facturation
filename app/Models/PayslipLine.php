<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayslipLine extends Model
{
    use HasFactory;
    protected $fillable = ['payslip_id', 'payroll_item_id', 'description', 'quantity', 'rate', 'amount'];
    protected $casts = ['quantity' => 'decimal:2', 'rate' => 'decimal:3', 'amount' => 'decimal:3'];
    public function payslip(): BelongsTo { return $this->belongsTo(Payslip::class); }
    public function payrollItem(): BelongsTo { return $this->belongsTo(PayrollItem::class); }
}
