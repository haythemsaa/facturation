<?php

namespace App\Console\Commands;

use App\Jobs\CheckLowStockJob;
use App\Models\Tenant;
use Illuminate\Console\Command;

class CheckLowStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:check-low';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for low stock levels and send alerts';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking low stock levels...');

        $tenants = Tenant::where('is_active', true)
            ->whereHas('activeSubscription', function ($query) {
                $query->whereJsonContains('modules', 'stock');
            })
            ->get();

        $bar = $this->output->createProgressBar($tenants->count());
        $bar->start();

        foreach ($tenants as $tenant) {
            CheckLowStockJob::dispatch($tenant);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Queued low stock checks for {$tenants->count()} tenants!");

        return Command::SUCCESS;
    }
}
