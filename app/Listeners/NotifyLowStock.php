<?php

namespace App\Listeners;

use App\Events\LowStockDetected;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class NotifyLowStock implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(LowStockDetected $event): void
    {
        $product = $event->product;
        $stock = $event->stock;

        Log::warning("Low stock detected", [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'warehouse_id' => $stock->warehouse_id,
            'current_quantity' => $stock->quantity,
            'alert_threshold' => $product->stock_alert_threshold,
            'tenant_id' => $product->tenant_id,
        ]);

        // TODO: Envoyer email aux responsables stock
        // TODO: Créer notification in-app
        // TODO: Créer bon de commande automatique si configuré
    }
}
