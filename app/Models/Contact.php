<?php
namespace App\Models;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;
    protected $fillable = ['tenant_id', 'first_name', 'last_name', 'email', 'phone', 'mobile', 'company', 'job_title', 'address', 'city', 'postal_code', 'type', 'source', 'score', 'assigned_to', 'customer_id', 'notes', 'metadata'];
    protected $casts = ['is_active' => 'boolean', 'metadata' => 'array'];
    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function opportunities(): HasMany { return $this->hasMany(Opportunity::class); }
    public function activities(): HasMany { return $this->hasMany(Activity::class); }
    public function tags(): MorphToMany { return $this->morphToMany(Tag::class, 'taggable'); }
}
