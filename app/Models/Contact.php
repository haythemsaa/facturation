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

    // Scopes
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeLeads($query)
    {
        return $query->where('type', 'lead');
    }

    public function scopeProspects($query)
    {
        return $query->where('type', 'prospect');
    }

    public function scopeCustomers($query)
    {
        return $query->where('type', 'customer');
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeFromSource($query, string $source)
    {
        return $query->where('source', $source);
    }

    public function scopeHighScore($query, int $minScore = 70)
    {
        return $query->where('score', '>=', $minScore);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('company', 'like', "%{$search}%");
        });
    }
}
