<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IrppDeclaration extends Model
{
    use HasFactory, BelongsToTenant;
    protected $fillable = ['tenant_id', 'month', 'year', 'total_gross_salaries', 'total_irpp_withheld', 'declaration_date', 'payment_date', 'status', 'reference', 'details'];
    protected $casts = ['total_gross_salaries' => 'decimal:3', 'total_irpp_withheld' => 'decimal:3', 'declaration_date' => 'date', 'payment_date' => 'date', 'details' => 'array'];
}
