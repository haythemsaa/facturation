<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Customer, Product, Document, Employee, Contact, Opportunity};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $modules = $tenant->activeSubscription->modules ?? [];

        $stats = [];

        // Stock & Facturation stats
        if (in_array('stock', $modules)) {
            $stats['stock'] = [
                'total_products' => Product::count(),
                'low_stock_products' => Product::whereColumn('stock_alert_threshold', '>=', DB::raw('(SELECT COALESCE(SUM(quantity), 0) FROM stocks WHERE products.id = stocks.product_id)'))->count(),
                'total_customers' => Customer::count(),
                'total_invoices' => Document::where('type', 'invoice')->count(),
                'total_revenue' => Document::where('type', 'invoice')->where('status', 'paid')->sum('total_ttc'),
                'pending_invoices' => Document::where('type', 'invoice')->whereIn('status', ['sent', 'partially_paid'])->count(),
            ];
        }

        // CRM stats
        if (in_array('crm', $modules)) {
            $stats['crm'] = [
                'total_contacts' => Contact::count(),
                'total_leads' => Contact::where('type', 'lead')->count(),
                'total_opportunities' => Opportunity::count(),
                'open_opportunities' => Opportunity::where('status', 'open')->count(),
                'opportunities_value' => Opportunity::where('status', 'open')->sum('value'),
                'won_opportunities' => Opportunity::where('status', 'won')->whereMonth('closed_date', now()->month)->count(),
            ];
        }

        // HR stats
        if (in_array('hr', $modules)) {
            $stats['hr'] = [
                'total_employees' => Employee::count(),
                'active_employees' => Employee::where('status', 'active')->count(),
                'on_leave_today' => Employee::where('status', 'on_leave')->count(),
                'pending_leave_requests' => \App\Models\LeaveRequest::where('status', 'pending')->count(),
                'payslips_current_month' => \App\Models\Payslip::where('month', now()->month)->where('year', now()->year)->count(),
            ];
        }

        return response()->json($stats);
    }

    public function charts(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $modules = $tenant->activeSubscription->modules ?? [];

        $charts = [];

        // Sales chart (last 12 months)
        if (in_array('stock', $modules)) {
            $salesData = Document::where('type', 'invoice')
                ->where('date', '>=', now()->subMonths(12))
                ->selectRaw('MONTH(date) as month, YEAR(date) as year, SUM(total_ttc) as total')
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();

            $charts['sales'] = $salesData;
        }

        // Opportunities pipeline
        if (in_array('crm', $modules)) {
            $pipelineData = Opportunity::where('status', 'open')
                ->with('stage')
                ->get()
                ->groupBy('stage.name')
                ->map(function ($opportunities, $stage) {
                    return [
                        'stage' => $stage,
                        'count' => $opportunities->count(),
                        'value' => $opportunities->sum('value'),
                    ];
                });

            $charts['pipeline'] = $pipelineData->values();
        }

        return response()->json($charts);
    }
}
