<?php

namespace App\Listeners;

use App\Events\OpportunityWon;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateWonOpportunityActivity implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(OpportunityWon $event): void
    {
        $opportunity = $event->opportunity;

        // Créer une activité pour le contact
        if ($opportunity->contact) {
            $opportunity->contact->activities()->create([
                'type' => 'opportunity_won',
                'description' => "Opportunité '{$opportunity->title}' gagnée ! Valeur: {$opportunity->value} TND",
                'performed_by' => $opportunity->assigned_to_id ?? auth()->id(),
                'tenant_id' => $opportunity->tenant_id,
            ]);
        }

        // TODO: Convertir le contact en customer si lead/prospect
        // TODO: Envoyer félicitations à l'équipe
        // TODO: Créer facture automatique si configuré
    }
}
