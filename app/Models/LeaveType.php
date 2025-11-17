<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use HasFactory, BelongsToTenant;
    protected $fillable = ['tenant_id', 'name', 'description', 'default_days', 'is_paid', 'requires_approval', 'requires_document', 'is_active'];
    protected $casts = ['is_paid' => 'boolean', 'requires_approval' => 'boolean', 'requires_document' => 'boolean', 'is_active' => 'boolean'];
    public function requests(): HasMany { return $this->hasMany(LeaveRequest::class); }
    public function balances(): HasMany { return $this->hasMany(LeaveBalance::class); }
}
