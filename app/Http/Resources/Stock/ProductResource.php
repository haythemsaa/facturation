<?php

namespace App\Http\Resources\Stock;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'code' => $this->code,
            'barcode' => $this->barcode,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'unit' => $this->unit,
            'purchase_price' => (float) $this->purchase_price,
            'selling_price' => (float) $this->selling_price,
            'minimum_price' => $this->minimum_price ? (float) $this->minimum_price : null,
            'tva_rate' => $this->tva_rate,
            'stock_alert_threshold' => $this->stock_alert_threshold ? (float) $this->stock_alert_threshold : null,
            'track_stock' => (bool) $this->track_stock,
            'is_active' => (bool) $this->is_active,
            'image' => $this->image,
            'metadata' => $this->metadata,

            // Relations
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'code' => $this->category->code,
                ];
            }),

            'stocks' => $this->whenLoaded('stocks'),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
