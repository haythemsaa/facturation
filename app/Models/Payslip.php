<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payslip extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;
    protected $fillable = ['tenant_id', 'employee_id', 'month', 'year', 'payment_date', 'base_salary', 'gross_salary', 'total_earnings', 'total_deductions', 'cnss_employee', 'cnss_employer', 'irpp', 'css', 'tfp', 'foprolos', 'net_salary', 'worked_days', 'worked_hours', 'overtime_hours', 'status', 'details'];
    protected $casts = ['payment_date' => 'date', 'base_salary' => 'decimal:3', 'gross_salary' => 'decimal:3', 'total_earnings' => 'decimal:3', 'total_deductions' => 'decimal:3', 'cnss_employee' => 'decimal:3', 'cnss_employer' => 'decimal:3', 'irpp' => 'decimal:3', 'css' => 'decimal:3', 'tfp' => 'decimal:3', 'foprolos' => 'decimal:3', 'net_salary' => 'decimal:3', 'worked_hours' => 'decimal:2', 'overtime_hours' => 'decimal:2', 'details' => 'array'];
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function lines(): HasMany { return $this->hasMany(PayslipLine::class); }
}
