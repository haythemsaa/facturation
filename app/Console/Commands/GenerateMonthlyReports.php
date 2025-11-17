<?php

namespace App\Console\Commands;

use App\Jobs\GenerateMonthlyReportJob;
use App\Models\Tenant;
use Illuminate\Console\Command;

class GenerateMonthlyReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:generate-monthly {--month= : Month (1-12)} {--year= : Year}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly reports for all active tenants';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $month = $this->option('month') ?? now()->subMonth()->month;
        $year = $this->option('year') ?? now()->subMonth()->year;

        $this->info("Generating monthly reports for {$year}-{$month}...");

        $activeTenants = Tenant::where('is_active', true)
            ->whereHas('activeSubscription')
            ->get();

        $bar = $this->output->createProgressBar($activeTenants->count());
        $bar->start();

        foreach ($activeTenants as $tenant) {
            GenerateMonthlyReportJob::dispatch($tenant, $year, $month);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Queued reports for {$activeTenants->count()} tenants!");

        return Command::SUCCESS;
    }
}
