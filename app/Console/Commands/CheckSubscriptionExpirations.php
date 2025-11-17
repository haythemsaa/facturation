<?php

namespace App\Console\Commands;

use App\Events\SubscriptionExpiring;
use App\Models\Subscription;
use Illuminate\Console\Command;

class CheckSubscriptionExpirations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-expirations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expiring subscriptions and send alerts';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking subscription expirations...');

        $alertDays = [30, 15, 7, 3, 1]; // Days before expiration to alert

        foreach ($alertDays as $days) {
            $targetDate = now()->addDays($days)->toDateString();

            $subscriptions = Subscription::where('status', 'active')
                ->whereDate('ends_at', $targetDate)
                ->get();

            foreach ($subscriptions as $subscription) {
                event(new SubscriptionExpiring($subscription, $days));
                $this->info("Alert sent for subscription #{$subscription->id} - {$days} days remaining");
            }
        }

        // Mark expired subscriptions
        $expired = Subscription::where('status', 'active')
            ->where('ends_at', '<', now())
            ->update(['status' => 'expired']);

        if ($expired > 0) {
            $this->warn("Marked {$expired} subscriptions as expired");
        }

        $this->info('Subscription check completed!');
        return Command::SUCCESS;
    }
}
