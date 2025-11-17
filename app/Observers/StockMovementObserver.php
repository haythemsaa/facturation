<?php

namespace App\Observers;

use App\Models\StockMovement;
use App\Models\Stock;

class StockMovementObserver
{
    /**
     * Handle the StockMovement "created" event.
     * Update stock quantity automatically.
     */
    public function created(StockMovement $movement): void
    {
        $this->updateStockQuantity($movement);
    }

    /**
     * Handle the StockMovement "deleted" event.
     * Reverse the stock quantity change.
     */
    public function deleted(StockMovement $movement): void
    {
        $this->updateStockQuantity($movement, reverse: true);
    }

    /**
     * Update stock quantity based on movement type.
     */
    private function updateStockQuantity(StockMovement $movement, bool $reverse = false): void
    {
        $stock = Stock::where('product_id', $movement->product_id)
            ->where('warehouse_id', $movement->warehouse_id)
            ->first();

        if (!$stock) {
            // Create stock record if it doesn't exist
            $stock = Stock::create([
                'product_id' => $movement->product_id,
                'warehouse_id' => $movement->warehouse_id,
                'quantity' => 0,
                'reserved_quantity' => 0,
                'tenant_id' => $movement->tenant_id,
            ]);
        }

        // Calculate quantity change based on movement type
        $quantityChange = match($movement->type) {
            'in', 'adjustment_in', 'transfer_in', 'return' => $movement->quantity,
            'out', 'adjustment_out', 'transfer_out', 'sale' => -$movement->quantity,
            default => 0
        };

        // Reverse if deleting
        if ($reverse) {
            $quantityChange = -$quantityChange;
        }

        // Update stock quantity
        $stock->quantity += $quantityChange;
        $stock->save();
    }
}
