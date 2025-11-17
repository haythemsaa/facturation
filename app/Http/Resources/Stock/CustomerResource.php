<?php

namespace App\Http\Resources\Stock;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'type' => $this->type,
            'code' => $this->code,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'address' => $this->address,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'tax_id' => $this->tax_id,
            'payment_terms' => $this->payment_terms,
            'credit_limit' => (float) $this->credit_limit,
            'discount_rate' => (float) $this->discount_rate,
            'is_active' => (bool) $this->is_active,
            'notes' => $this->notes,
            'total_revenue' => $this->whenLoaded('documents', function () {
                return (float) $this->documents->where('type', 'invoice')->sum('total_ttc');
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
