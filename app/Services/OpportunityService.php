<?php

namespace App\Services;

use App\Events\OpportunityWon;
use App\Models\Contact;
use App\Models\Opportunity;
use Illuminate\Support\Facades\DB;

class OpportunityService
{
    /**
     * Mark opportunity as won and convert contact to customer.
     */
    public function markAsWon(Opportunity $opportunity): Opportunity
    {
        return DB::transaction(function () use ($opportunity) {
            $opportunity->status = 'won';
            $opportunity->actual_close_date = now();
            $opportunity->save();

            // Convert contact to customer if lead or prospect
            if ($opportunity->contact && in_array($opportunity->contact->type, ['lead', 'prospect'])) {
                $this->convertContactToCustomer($opportunity->contact);
            }

            // Dispatch event
            event(new OpportunityWon($opportunity));

            return $opportunity->fresh();
        });
    }

    /**
     * Mark opportunity as lost with reason.
     */
    public function markAsLost(Opportunity $opportunity, string $reason): Opportunity
    {
        $opportunity->status = 'lost';
        $opportunity->lost_reason = $reason;
        $opportunity->actual_close_date = now();
        $opportunity->save();

        // Log activity
        $opportunity->contact?->activities()->create([
            'type' => 'opportunity_lost',
            'description' => "Opportunité '{$opportunity->title}' perdue. Raison: {$reason}",
            'performed_by' => auth()->id(),
            'tenant_id' => $opportunity->tenant_id,
        ]);

        return $opportunity->fresh();
    }

    /**
     * Move opportunity to next pipeline stage.
     */
    public function moveToNextStage(Opportunity $opportunity): Opportunity
    {
        $currentStage = $opportunity->stage;
        if (!$currentStage) {
            throw new \RuntimeException('Opportunity has no current stage');
        }

        // Find next stage in pipeline
        $nextStage = $currentStage->pipeline->stages()
            ->where('order', '>', $currentStage->order)
            ->orderBy('order')
            ->first();

        if (!$nextStage) {
            throw new \RuntimeException('No next stage available');
        }

        $opportunity->stage_id = $nextStage->id;
        $opportunity->probability = $nextStage->probability ?? $opportunity->probability;
        $opportunity->save();

        // Log activity
        $opportunity->contact?->activities()->create([
            'type' => 'opportunity_stage_changed',
            'description' => "Opportunité déplacée de '{$currentStage->name}' vers '{$nextStage->name}'",
            'performed_by' => auth()->id(),
            'tenant_id' => $opportunity->tenant_id,
        ]);

        return $opportunity->fresh('stage');
    }

    /**
     * Calculate weighted pipeline value.
     */
    public function calculateWeightedValue(int $pipelineId): float
    {
        return Opportunity::inPipeline($pipelineId)
            ->open()
            ->get()
            ->sum(fn($opp) => $opp->value * ($opp->probability / 100));
    }

    /**
     * Convert contact to customer.
     */
    private function convertContactToCustomer(Contact $contact): void
    {
        $contact->type = 'customer';
        $contact->save();

        $contact->activities()->create([
            'type' => 'contact_converted',
            'description' => "Contact converti en client suite à opportunité gagnée",
            'performed_by' => auth()->id(),
            'tenant_id' => $contact->tenant_id,
        ]);
    }
}
