<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Opportunity;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ReportingService
{
    /**
     * Get sales report for period.
     */
    public function getSalesReport(string $startDate, string $endDate): array
    {
        $invoices = Document::invoices()
            ->validated()
            ->dateBetween($startDate, $endDate)
            ->get();

        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'total_invoices' => $invoices->count(),
            'total_revenue_ht' => $invoices->sum('total_ht'),
            'total_revenue_ttc' => $invoices->sum('total_ttc'),
            'total_tva_collected' => $invoices->sum('total_tva'),
            'paid_invoices' => $invoices->where('payment_status', 'paid')->count(),
            'pending_invoices' => $invoices->where('payment_status', 'pending')->count(),
            'total_paid' => $invoices->where('payment_status', 'paid')->sum('total_ttc'),
            'total_pending' => $invoices->where('payment_status', 'pending')->sum('total_ttc'),
            'average_invoice_value' => $invoices->avg('total_ttc'),
        ];
    }

    /**
     * Get CRM pipeline report.
     */
    public function getPipelineReport(int $pipelineId): array
    {
        $opportunities = Opportunity::inPipeline($pipelineId)->get();

        $byStage = $opportunities->groupBy('stage_id')->map(function ($opps) {
            return [
                'count' => $opps->count(),
                'total_value' => $opps->sum('value'),
                'weighted_value' => $opps->sum(fn($o) => $o->value * ($o->probability / 100)),
            ];
        });

        return [
            'pipeline_id' => $pipelineId,
            'total_opportunities' => $opportunities->count(),
            'total_value' => $opportunities->sum('value'),
            'weighted_value' => $opportunities->sum(fn($o) => $o->value * ($o->probability / 100)),
            'by_stage' => $byStage,
            'by_status' => [
                'open' => $opportunities->where('status', 'open')->count(),
                'won' => $opportunities->where('status', 'won')->count(),
                'lost' => $opportunities->where('status', 'lost')->count(),
            ],
            'win_rate' => $opportunities->where('status', '!=', 'open')->count() > 0
                ? ($opportunities->where('status', 'won')->count() / $opportunities->where('status', '!=', 'open')->count()) * 100
                : 0,
        ];
    }

    /**
     * Get stock valuation report.
     */
    public function getStockValuationReport(): array
    {
        $products = Product::with('stocks')->trackedStock()->active()->get();

        $totalValue = 0;
        $productDetails = $products->map(function ($product) use (&$totalValue) {
            $totalQuantity = $product->stocks->sum('quantity');
            $value = $totalQuantity * $product->purchase_price;
            $totalValue += $value;

            return [
                'product_id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'total_quantity' => $totalQuantity,
                'purchase_price' => $product->purchase_price,
                'total_value' => $value,
            ];
        });

        return [
            'total_products' => $products->count(),
            'total_stock_value' => $totalValue,
            'products' => $productDetails,
            'low_stock_items' => Product::lowStock()->count(),
        ];
    }

    /**
     * Get top customers by revenue.
     */
    public function getTopCustomers(int $limit = 10, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = DB::table('documents')
            ->select('customer_id', DB::raw('SUM(total_ttc) as total_revenue'), DB::raw('COUNT(*) as invoice_count'))
            ->where('type', 'invoice')
            ->where('is_validated', true)
            ->whereNotNull('customer_id')
            ->groupBy('customer_id')
            ->orderByDesc('total_revenue')
            ->limit($limit);

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        return $query->get()->map(function ($row) {
            $customer = \App\Models\Customer::find($row->customer_id);
            return [
                'customer_id' => $row->customer_id,
                'customer_name' => $customer?->name,
                'total_revenue' => $row->total_revenue,
                'invoice_count' => $row->invoice_count,
                'average_order_value' => $row->total_revenue / $row->invoice_count,
            ];
        })->toArray();
    }
}
