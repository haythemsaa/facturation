<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\ReportingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Tenant $tenant,
        public int $year,
        public int $month
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ReportingService $reportingService): void
    {
        $startDate = sprintf('%04d-%02d-01', $this->year, $this->month);
        $endDate = date('Y-m-t', strtotime($startDate));

        Log::info("Generating monthly report for tenant {$this->tenant->id}", [
            'tenant' => $this->tenant->name,
            'period' => "{$this->year}-{$this->month}",
        ]);

        // Generate sales report
        $salesReport = $reportingService->getSalesReport($startDate, $endDate);

        // Generate stock valuation
        $stockReport = $reportingService->getStockValuationReport();

        // Generate top customers
        $topCustomers = $reportingService->getTopCustomers(10, $startDate, $endDate);

        // TODO: Save report to database
        // TODO: Generate PDF
        // TODO: Send email to tenant admins

        Log::info("Monthly report generated successfully", [
            'tenant_id' => $this->tenant->id,
            'total_revenue' => $salesReport['total_revenue_ttc'],
        ]);
    }
}
