<?php

namespace App\Providers;

use App\Events\DocumentValidated;
use App\Events\LowStockDetected;
use App\Events\OpportunityWon;
use App\Events\SubscriptionExpiring;
use App\Listeners\CreateWonOpportunityActivity;
use App\Listeners\NotifyLowStock;
use App\Listeners\SendDocumentValidatedNotification;
use App\Listeners\SendSubscriptionExpiringAlert;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        DocumentValidated::class => [
            SendDocumentValidatedNotification::class,
        ],
        OpportunityWon::class => [
            CreateWonOpportunityActivity::class,
        ],
        SubscriptionExpiring::class => [
            SendSubscriptionExpiringAlert::class,
        ],
        LowStockDetected::class => [
            NotifyLowStock::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
