<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CnssDeclaration extends Model
{
    use HasFactory, BelongsToTenant;
    protected $fillable = ['tenant_id', 'month', 'year', 'total_salaries', 'total_employee_contributions', 'total_employer_contributions', 'total_amount', 'declaration_date', 'payment_date', 'status', 'reference', 'details'];
    protected $casts = ['total_salaries' => 'decimal:3', 'total_employee_contributions' => 'decimal:3', 'total_employer_contributions' => 'decimal:3', 'total_amount' => 'decimal:3', 'declaration_date' => 'date', 'payment_date' => 'date', 'details' => 'array'];
}
