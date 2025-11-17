<?php

namespace App\Console\Commands;

use App\Jobs\SendPaymentReminderJob;
use App\Models\Document;
use Illuminate\Console\Command;

class SendPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:send-reminders {--days=7 : Days overdue to send reminder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send payment reminders for overdue invoices';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $daysOverdue = $this->option('days');
        $this->info("Sending reminders for invoices {$daysOverdue}+ days overdue...");

        $overdueInvoices = Document::invoices()
            ->validated()
            ->where('payment_status', '!=', 'paid')
            ->where('due_date', '<=', now()->subDays($daysOverdue))
            ->get();

        $bar = $this->output->createProgressBar($overdueInvoices->count());
        $bar->start();

        foreach ($overdueInvoices as $invoice) {
            // Dispatch job to queue
            SendPaymentReminderJob::dispatch($invoice);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Queued {$overdueInvoices->count()} payment reminders!");

        return Command::SUCCESS;
    }
}
