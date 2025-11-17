<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;
    protected $fillable = ['tenant_id', 'code', 'type', 'name', 'contact_name', 'matricule_fiscal', 'email', 'phone', 'mobile', 'address', 'city', 'postal_code', 'credit_limit', 'payment_terms', 'discount_rate', 'is_active', 'metadata'];
    protected $casts = ['credit_limit' => 'decimal:3', 'discount_rate' => 'decimal:2', 'is_active' => 'boolean', 'metadata' => 'array'];
    public function documents(): HasMany { return $this->hasMany(Document::class); }
}
