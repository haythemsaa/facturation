<?php

namespace App\Providers;

use App\Models\Document;
use App\Models\Opportunity;
use App\Models\StockMovement;
use App\Models\Subscription;
use App\Observers\DocumentObserver;
use App\Observers\OpportunityObserver;
use App\Observers\StockMovementObserver;
use App\Observers\SubscriptionObserver;
use Illuminate\Support\ServiceProvider;
use App\Services\NotificationService;
use App\Services\PdfService;
use App\Services\ExcelService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register services as singletons
        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService();
        });

        $this->app->singleton(PdfService::class, function ($app) {
            return new PdfService();
        });

        $this->app->singleton(ExcelService::class, function ($app) {
            return new ExcelService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers
        Document::observe(DocumentObserver::class);
        StockMovement::observe(StockMovementObserver::class);
        Opportunity::observe(OpportunityObserver::class);
        Subscription::observe(SubscriptionObserver::class);
    }
}
