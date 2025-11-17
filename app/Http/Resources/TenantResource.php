<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'company_name' => $this->company_name,
            'tax_id' => $this->tax_id,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'is_active' => (bool) $this->is_active,
            'logo' => $this->logo,

            // Active subscription
            'subscription' => $this->whenLoaded('activeSubscription', function () {
                return [
                    'id' => $this->activeSubscription->id,
                    'plan' => $this->activeSubscription->plan,
                    'status' => $this->activeSubscription->status,
                    'modules' => $this->activeSubscription->modules,
                    'starts_at' => $this->activeSubscription->starts_at?->toISOString(),
                    'ends_at' => $this->activeSubscription->ends_at?->toISOString(),
                    'days_remaining' => $this->activeSubscription->ends_at?->diffInDays(now()),
                ];
            }),

            // Stats
            'users_count' => $this->whenLoaded('users', fn() => $this->users->count()),
            'products_count' => $this->whenCounted('products'),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
