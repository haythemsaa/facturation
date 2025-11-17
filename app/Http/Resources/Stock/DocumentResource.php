<?php

namespace App\Http\Resources\Stock;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
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
            'number' => $this->number,
            'date' => $this->date?->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'status' => $this->status,

            // Amounts
            'subtotal' => (float) $this->subtotal,
            'discount_amount' => (float) $this->discount_amount,
            'discount_rate' => (float) $this->discount_rate,
            'total_ht' => (float) $this->total_ht,
            'total_tva' => (float) $this->total_tva,
            'timbre_fiscal' => (float) $this->timbre_fiscal,
            'total_ttc' => (float) $this->total_ttc,

            'note' => $this->note,
            'terms' => $this->terms,

            // Relations
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'code' => $this->customer->code,
                    'name' => $this->customer->name,
                    'email' => $this->customer->email,
                ];
            }),

            'supplier' => $this->whenLoaded('supplier', function () {
                return [
                    'id' => $this->supplier->id,
                    'code' => $this->supplier->code,
                    'name' => $this->supplier->name,
                ];
            }),

            'warehouse' => $this->whenLoaded('warehouse', function () {
                return [
                    'id' => $this->warehouse->id,
                    'code' => $this->warehouse->code,
                    'name' => $this->warehouse->name,
                ];
            }),

            'lines' => $this->whenLoaded('lines'),
            'payments' => $this->whenLoaded('payments'),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
