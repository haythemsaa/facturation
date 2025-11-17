<?php

namespace App\Policies;

use App\Models\Opportunity;
use App\Models\User;

class OpportunityPolicy
{
    /**
     * Determine if the user can view any opportunities.
     */
    public function viewAny(User $user): bool
    {
        $subscription = $user->tenant->activeSubscription;

        return $subscription
            && $subscription->hasModule('crm')
            && $user->hasPermissionTo('view_opportunities');
    }

    /**
     * Determine if the user can view the opportunity.
     */
    public function view(User $user, Opportunity $opportunity): bool
    {
        return $user->tenant_id === $opportunity->tenant_id
            && ($user->hasPermissionTo('view_opportunities') || $opportunity->assigned_to_id === $user->id);
    }

    /**
     * Determine if the user can create opportunities.
     */
    public function create(User $user): bool
    {
        $subscription = $user->tenant->activeSubscription;

        return $subscription
            && $subscription->hasModule('crm')
            && $user->hasPermissionTo('create_opportunities');
    }

    /**
     * Determine if the user can update the opportunity.
     */
    public function update(User $user, Opportunity $opportunity): bool
    {
        // Cannot edit closed opportunities unless admin
        if (in_array($opportunity->status, ['won', 'lost']) && !$user->hasRole('admin')) {
            return false;
        }

        return $user->tenant_id === $opportunity->tenant_id
            && ($user->hasPermissionTo('edit_opportunities') || $opportunity->assigned_to_id === $user->id);
    }

    /**
     * Determine if the user can delete the opportunity.
     */
    public function delete(User $user, Opportunity $opportunity): bool
    {
        // Cannot delete won opportunities
        if ($opportunity->status === 'won') {
            return false;
        }

        return $user->tenant_id === $opportunity->tenant_id
            && $user->hasPermissionTo('delete_opportunities');
    }

    /**
     * Determine if the user can close (win/lose) the opportunity.
     */
    public function close(User $user, Opportunity $opportunity): bool
    {
        return $user->tenant_id === $opportunity->tenant_id
            && ($user->hasRole(['admin', 'manager']) || $opportunity->assigned_to_id === $user->id)
            && $opportunity->status === 'open';
    }
}
