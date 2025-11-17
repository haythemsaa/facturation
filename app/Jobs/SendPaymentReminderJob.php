<?php

namespace App\Jobs;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPaymentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Document $document
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->document->payment_status === 'paid') {
            Log::info("Skipping payment reminder - invoice already paid", [
                'document_id' => $this->document->id,
                'number' => $this->document->number,
            ]);
            return;
        }

        $daysOverdue = now()->diffInDays($this->document->due_date, false);

        Log::info("Sending payment reminder", [
            'document_id' => $this->document->id,
            'number' => $this->document->number,
            'customer' => $this->document->customer?->name,
            'amount' => $this->document->total_ttc,
            'days_overdue' => abs($daysOverdue),
        ]);

        // TODO: Send email to customer
        // TODO: Create activity log
        // TODO: Update last_reminder_sent_at field
        // TODO: If >60 days overdue, notify management

        // Mark reminder as sent
        $this->document->last_reminder_sent_at = now();
        $this->document->save();
    }
}
