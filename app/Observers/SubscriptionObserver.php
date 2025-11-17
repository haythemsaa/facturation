<?php

namespace App\Observers;

use App\Models\Subscription;

class SubscriptionObserver
{
    /**
     * Handle the Subscription "creating" event.
     * Set default values.
     */
    public function creating(Subscription $subscription): void
    {
        // Set default status if not provided
        if (empty($subscription->status)) {
            $subscription->status = 'active';
        }

        // Set starts_at to now if not provided
        if (empty($subscription->starts_at)) {
            $subscription->starts_at = now();
        }

        // Calculate ends_at based on billing_cycle if not set
        if (empty($subscription->ends_at) && !empty($subscription->billing_cycle)) {
            $subscription->ends_at = match($subscription->billing_cycle) {
                'monthly' => now()->addMonth(),
                'quarterly' => now()->addMonths(3),
                'yearly' => now()->addYear(),
                default => now()->addMonth()
            };
        }
    }

    /**
     * Handle the Subscription "updated" event.
     * Handle status changes and cancellations.
     */
    public function updated(Subscription $subscription): void
    {
        // If status changed to cancelled, set cancelled_at
        if ($subscription->isDirty('status') && $subscription->status === 'cancelled') {
            if (empty($subscription->cancelled_at)) {
                $subscription->cancelled_at = now();
                $subscription->saveQuietly();
            }
        }

        // Check if subscription expired
        if ($subscription->ends_at && $subscription->ends_at->isPast() && $subscription->status === 'active') {
            $subscription->status = 'expired';
            $subscription->saveQuietly();
        }
    }

    /**
     * Handle the Subscription "created" event.
     * Deactivate other active subscriptions for the same tenant.
     */
    public function created(Subscription $subscription): void
    {
        if ($subscription->status === 'active') {
            // Deactivate other active subscriptions for this tenant
            Subscription::where('tenant_id', $subscription->tenant_id)
                ->where('id', '!=', $subscription->id)
                ->where('status', 'active')
                ->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);
        }
    }
}
