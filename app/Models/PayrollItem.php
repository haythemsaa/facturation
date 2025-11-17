<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    use HasFactory, BelongsToTenant;
    protected $fillable = ['tenant_id', 'name', 'code', 'type', 'category', 'is_taxable', 'is_cnss_subject', 'default_amount', 'is_fixed', 'calculation_formula', 'is_active'];
    protected $casts = ['is_taxable' => 'boolean', 'is_cnss_subject' => 'boolean', 'default_amount' => 'decimal:3', 'is_fixed' => 'boolean', 'is_active' => 'boolean'];
}
