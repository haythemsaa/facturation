<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;

class ContactPolicy
{
    /**
     * Determine if the user can view any contacts.
     */
    public function viewAny(User $user): bool
    {
        $subscription = $user->tenant->activeSubscription;

        return $subscription
            && $subscription->hasModule('crm')
            && $user->hasPermissionTo('view_contacts');
    }

    /**
     * Determine if the user can view the contact.
     */
    public function view(User $user, Contact $contact): bool
    {
        // User can view if in same tenant and (has permission OR is assigned)
        return $user->tenant_id === $contact->tenant_id
            && ($user->hasPermissionTo('view_contacts') || $contact->assigned_to === $user->id);
    }

    /**
     * Determine if the user can create contacts.
     */
    public function create(User $user): bool
    {
        $subscription = $user->tenant->activeSubscription;

        return $subscription
            && $subscription->hasModule('crm')
            && $user->hasPermissionTo('create_contacts');
    }

    /**
     * Determine if the user can update the contact.
     */
    public function update(User $user, Contact $contact): bool
    {
        return $user->tenant_id === $contact->tenant_id
            && ($user->hasPermissionTo('edit_contacts') || $contact->assigned_to === $user->id);
    }

    /**
     * Determine if the user can delete the contact.
     */
    public function delete(User $user, Contact $contact): bool
    {
        // Cannot delete if contact has opportunities
        if ($contact->opportunities()->exists()) {
            return false;
        }

        return $user->tenant_id === $contact->tenant_id
            && $user->hasPermissionTo('delete_contacts');
    }

    /**
     * Determine if the user can assign the contact to others.
     */
    public function assign(User $user, Contact $contact): bool
    {
        return $user->tenant_id === $contact->tenant_id
            && $user->hasRole(['admin', 'manager']);
    }
}
