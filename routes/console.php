<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
*/

// Backup automatique quotidien à 2h du matin
Schedule::command('backup:run')
    ->dailyAt('02:00')
    ->timezone('Africa/Tunis')
    ->appendOutputTo(storage_path('logs/backup.log'));

// Nettoyage des vieux audit logs (> 365 jours) - hebdomadaire dimanche 3h
Schedule::call(function () {
    $retentionDays = config('app.audit_log_retention_days', 365);
    \App\Models\AuditLog::where('created_at', '<', now()->subDays($retentionDays))->delete();
})->weekly()->sundays()->at('03:00');

// Nettoyage des notifications lues (> 90 jours) - hebdomadaire dimanche 4h
Schedule::call(function () {
    \App\Models\Notification::whereNotNull('read_at')
        ->where('read_at', '<', now()->subDays(90))
        ->delete();
})->weekly()->sundays()->at('04:00');

// Vérifier les abonnements expirant dans 7 jours - quotidien 9h
Schedule::call(function () {
    $expiringSoon = \App\Models\Subscription::where('status', 'active')
        ->whereBetween('ends_at', [now()->addDays(7), now()->addDays(8)])
        ->get();

    foreach ($expiringSoon as $subscription) {
        event(new \App\Events\SubscriptionExpiring($subscription));
    }
})->dailyAt('09:00');

// Alertes stock faible - quotidien 10h
Schedule::call(function () {
    $threshold = config('app.low_stock_threshold', 10);

    $lowStockProducts = \App\Models\Stock::with('product', 'tenant')
        ->whereHas('product', function ($query) {
            $query->where('track_stock', true);
        })
        ->whereRaw('quantity - reserved_quantity < ?', [$threshold])
        ->get();

    foreach ($lowStockProducts->groupBy('tenant_id') as $tenantId => $stocks) {
        event(new \App\Events\LowStockDetected($tenantId, $stocks));
    }
})->dailyAt('10:00');

// Nettoyage des sessions expirées - quotidien
Schedule::command('auth:clear-resets')->daily();

// Nettoyage du cache - hebdomadaire
Schedule::command('cache:prune-stale-tags')->weekly();
