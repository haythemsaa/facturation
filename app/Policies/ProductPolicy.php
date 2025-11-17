<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine if the user can view any products.
     */
    public function viewAny(User $user): bool
    {
        $subscription = $user->tenant->activeSubscription;

        return $subscription
            && $subscription->hasModule('stock')
            && $user->hasPermissionTo('view_products');
    }

    /**
     * Determine if the user can view the product.
     */
    public function view(User $user, Product $product): bool
    {
        return $user->tenant_id === $product->tenant_id
            && $user->hasPermissionTo('view_products');
    }

    /**
     * Determine if the user can create products.
     */
    public function create(User $user): bool
    {
        $subscription = $user->tenant->activeSubscription;

        return $subscription
            && $subscription->hasModule('stock')
            && $user->hasPermissionTo('create_products');
    }

    /**
     * Determine if the user can update the product.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->tenant_id === $product->tenant_id
            && $user->hasPermissionTo('edit_products');
    }

    /**
     * Determine if the user can delete the product.
     */
    public function delete(User $user, Product $product): bool
    {
        // Cannot delete if product has stock movements or is in documents
        if ($product->stocks()->where('quantity', '>', 0)->exists()) {
            return false;
        }

        return $user->tenant_id === $product->tenant_id
            && $user->hasPermissionTo('delete_products');
    }
}
