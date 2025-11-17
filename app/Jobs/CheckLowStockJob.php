<?php

namespace App\Jobs;

use App\Events\LowStockDetected;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckLowStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Tenant $tenant
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Checking low stock for tenant {$this->tenant->id}");

        $lowStockProducts = Product::where('tenant_id', $this->tenant->id)
            ->trackedStock()
            ->active()
            ->with('stocks')
            ->get()
            ->filter(function ($product) {
                $totalStock = $product->stocks->sum('quantity');
                return $totalStock <= $product->stock_alert_threshold;
            });

        foreach ($lowStockProducts as $product) {
            foreach ($product->stocks as $stock) {
                if ($stock->quantity <= $product->stock_alert_threshold) {
                    event(new LowStockDetected($product, $stock));
                }
            }
        }

        Log::info("Low stock check completed", [
            'tenant_id' => $this->tenant->id,
            'low_stock_products' => $lowStockProducts->count(),
        ]);
    }
}
