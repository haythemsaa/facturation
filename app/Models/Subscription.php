<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'plan',
        'modules',
        'max_users',
        'max_documents_per_month',
        'price',
        'status',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'cancelled_at',
    ];

    protected $casts = [
        'modules' => 'array',
        'trial_ends_at' => 'date',
        'starts_at' => 'date',
        'ends_at' => 'date',
        'cancelled_at' => 'date',
        'price' => 'decimal:3',
    ];

    /**
     * Get the tenant that owns the subscription
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if subscription is on trial
     */
    public function isOnTrial(): bool
    {
        return $this->status === 'trial' &&
               $this->trial_ends_at &&
               $this->trial_ends_at->isFuture();
    }

    /**
     * Check if trial has ended
     */
    public function trialHasEnded(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    /**
     * Check if subscription has a specific module
     */
    public function hasModule(string $module): bool
    {
        return in_array($module, $this->modules ?? []);
    }

    /**
     * Activate the subscription
     */
    public function activate(): bool
    {
        return $this->update(['status' => 'active']);
    }

    /**
     * Suspend the subscription
     */
    public function suspend(): bool
    {
        return $this->update(['status' => 'suspended']);
    }

    /**
     * Cancel the subscription
     */
    public function cancel(): bool
    {
        return $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }
}
