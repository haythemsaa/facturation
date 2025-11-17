<?php

namespace App\Http\Resources\Stock;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
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
            'quantity' => (float) $this->quantity,
            'reserved_quantity' => (float) $this->reserved_quantity,
            'available_quantity' => (float) ($this->quantity - $this->reserved_quantity),

            // Product info
            'product' => $this->whenLoaded('product', fn() => [
                'id' => $this->product->id,
                'code' => $this->product->code,
                'name' => $this->product->name,
                'unit' => $this->product->unit,
                'stock_alert_threshold' => (float) $this->product->stock_alert_threshold,
                'is_low_stock' => $this->quantity <= $this->product->stock_alert_threshold,
            ]),

            // Warehouse info
            'warehouse' => $this->whenLoaded('warehouse', fn() => [
                'id' => $this->warehouse->id,
                'name' => $this->warehouse->name,
                'code' => $this->warehouse->code,
            ]),

            // Valuation
            'purchase_value' => $this->whenLoaded('product', fn() =>
                (float) $this->quantity * $this->product->purchase_price
            ),
            'selling_value' => $this->whenLoaded('product', fn() =>
                (float) $this->quantity * $this->product->selling_price
            ),

            'last_movement_at' => $this->last_movement_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
