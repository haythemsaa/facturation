<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;
    protected $fillable = ['tenant_id', 'code', 'name', 'contact_name', 'matricule_fiscal', 'email', 'phone', 'mobile', 'address', 'city', 'postal_code', 'payment_terms', 'is_active', 'metadata'];
    protected $casts = ['is_active' => 'boolean', 'metadata' => 'array'];
    public function documents(): HasMany { return $this->hasMany(Document::class); }
}
