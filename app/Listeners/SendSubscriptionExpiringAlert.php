<?php

namespace App\Listeners;

use App\Events\SubscriptionExpiring;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendSubscriptionExpiringAlert implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(SubscriptionExpiring $event): void
    {
        $subscription = $event->subscription;
        $daysRemaining = $event->daysRemaining;

        Log::warning("Subscription expiring soon", [
            'tenant_id' => $subscription->tenant_id,
            'subscription_id' => $subscription->id,
            'plan' => $subscription->plan,
            'days_remaining' => $daysRemaining,
            'ends_at' => $subscription->ends_at,
        ]);

        // TODO: Envoyer email d'alerte au tenant admin
        // TODO: Créer notification in-app
        // TODO: Si 3 jours restants, envoyer SMS
    }
}
