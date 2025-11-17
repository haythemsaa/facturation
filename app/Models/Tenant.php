<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'matricule_fiscal',
        'code_tva',
        'phone',
        'email',
        'address',
        'city',
        'postal_code',
        'country',
        'logo',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Get the active subscription for the tenant
     */
    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->where('status', 'active');
    }

    /**
     * Get all subscriptions for the tenant
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get all users for the tenant
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if tenant has an active subscription
     */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription()->exists();
    }

    /**
     * Check if tenant has access to a specific module
     */
    public function hasModule(string $module): bool
    {
        $subscription = $this->activeSubscription;

        if (!$subscription) {
            return false;
        }

        $modules = $subscription->modules ?? [];
        return in_array($module, $modules);
    }

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
