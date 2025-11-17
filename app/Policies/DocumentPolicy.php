<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Determine if the user can view any documents.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_documents');
    }

    /**
     * Determine if the user can view the document.
     */
    public function view(User $user, Document $document): bool
    {
        // User must be in same tenant and have permission
        return $user->tenant_id === $document->tenant_id
            && $user->hasPermissionTo('view_documents');
    }

    /**
     * Determine if the user can create documents.
     */
    public function create(User $user): bool
    {
        // Check stock module access
        $subscription = $user->tenant->activeSubscription;

        return $subscription
            && $subscription->hasModule('stock')
            && $user->hasPermissionTo('create_documents');
    }

    /**
     * Determine if the user can update the document.
     */
    public function update(User $user, Document $document): bool
    {
        // Cannot edit if validated or paid
        if ($document->is_validated || $document->payment_status === 'paid') {
            return false;
        }

        return $user->tenant_id === $document->tenant_id
            && $user->hasPermissionTo('edit_documents');
    }

    /**
     * Determine if the user can delete the document.
     */
    public function delete(User $user, Document $document): bool
    {
        // Cannot delete if validated or paid
        if ($document->is_validated || $document->payment_status === 'paid') {
            return false;
        }

        return $user->tenant_id === $document->tenant_id
            && $user->hasPermissionTo('delete_documents');
    }

    /**
     * Determine if the user can validate the document.
     */
    public function validate(User $user, Document $document): bool
    {
        return $user->tenant_id === $document->tenant_id
            && $user->hasPermissionTo('validate_documents')
            && !$document->is_validated;
    }
}
