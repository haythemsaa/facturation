<?php

namespace App\Observers;

use App\Models\Opportunity;

class OpportunityObserver
{
    /**
     * Handle the Opportunity "updating" event.
     * Auto-set actual_close_date when status changes to won or lost.
     */
    public function updating(Opportunity $opportunity): void
    {
        // If status is changing to won or lost, set actual close date
        if ($opportunity->isDirty('status')) {
            $newStatus = $opportunity->status;
            $oldStatus = $opportunity->getOriginal('status');

            if (in_array($newStatus, ['won', 'lost']) && $oldStatus === 'open') {
                if (empty($opportunity->actual_close_date)) {
                    $opportunity->actual_close_date = now();
                }
            }

            // If reopening, clear actual close date
            if ($newStatus === 'open' && in_array($oldStatus, ['won', 'lost'])) {
                $opportunity->actual_close_date = null;
                $opportunity->lost_reason = null;
            }
        }

        // Ensure lost_reason is set only when status is lost
        if ($opportunity->status !== 'lost') {
            $opportunity->lost_reason = null;
        }
    }

    /**
     * Handle the Opportunity "created" event.
     * Set default probability based on pipeline stage.
     */
    public function created(Opportunity $opportunity): void
    {
        // Create initial activity log
        if ($opportunity->contact) {
            $opportunity->contact->activities()->create([
                'type' => 'opportunity_created',
                'description' => "Opportunité '{$opportunity->title}' créée avec une valeur de {$opportunity->value} TND",
                'performed_by' => auth()->id(),
                'tenant_id' => $opportunity->tenant_id,
            ]);
        }
    }
}
