<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class HealthController extends Controller
{
    /**
     * Basic health check endpoint.
     */
    public function index()
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'service' => config('app.name'),
            'version' => config('app.version', '1.0.0'),
        ]);
    }

    /**
     * Detailed health check with all components.
     */
    public function detailed()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
        ];

        $overall = collect($checks)->every(fn($check) => $check['status'] === 'healthy')
            ? 'healthy'
            : 'unhealthy';

        return response()->json([
            'status' => $overall,
            'timestamp' => now()->toISOString(),
            'checks' => $checks,
            'system' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'environment' => config('app.env'),
                'debug_mode' => config('app.debug'),
            ],
        ], $overall === 'healthy' ? 200 : 503);
    }

    /**
     * Check database connectivity.
     */
    protected function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            $tablesCount = DB::select('SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ?', [
                config('database.connections.pgsql.database')
            ])[0]->count ?? 0;

            return [
                'status' => 'healthy',
                'connection' => DB::connection()->getName(),
                'tables_count' => $tablesCount,
                'response_time' => $this->measureResponseTime(fn() => DB::select('SELECT 1')),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache system.
     */
    protected function checkCache(): array
    {
        try {
            $key = 'health_check_' . time();
            $value = 'test';

            Cache::put($key, $value, 10);
            $retrieved = Cache::get($key);
            Cache::forget($key);

            return [
                'status' => $retrieved === $value ? 'healthy' : 'unhealthy',
                'driver' => config('cache.default'),
                'write_read' => $retrieved === $value ? 'ok' : 'failed',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check storage accessibility.
     */
    protected function checkStorage(): array
    {
        try {
            $disk = config('filesystems.default');
            $testFile = 'health-check-test.txt';

            Storage::disk($disk)->put($testFile, 'test');
            $exists = Storage::disk($disk)->exists($testFile);
            Storage::disk($disk)->delete($testFile);

            return [
                'status' => $exists ? 'healthy' : 'unhealthy',
                'disk' => $disk,
                'writable' => $exists,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check queue system.
     */
    protected function checkQueue(): array
    {
        try {
            $driver = config('queue.default');

            return [
                'status' => 'healthy',
                'driver' => $driver,
                'note' => 'Queue configuration detected',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get system metrics.
     */
    public function metrics()
    {
        $tenants = DB::table('tenants')->count();
        $users = DB::table('users')->count();
        $documents = DB::table('documents')->count();
        $activeSubscriptions = DB::table('subscriptions')->where('status', 'active')->count();

        return response()->json([
            'timestamp' => now()->toISOString(),
            'metrics' => [
                'tenants' => [
                    'total' => $tenants,
                    'active' => DB::table('tenants')->where('is_active', true)->count(),
                ],
                'users' => [
                    'total' => $users,
                ],
                'subscriptions' => [
                    'active' => $activeSubscriptions,
                    'expired' => DB::table('subscriptions')->where('status', 'expired')->count(),
                    'cancelled' => DB::table('subscriptions')->where('status', 'cancelled')->count(),
                ],
                'documents' => [
                    'total' => $documents,
                    'invoices' => DB::table('documents')->where('type', 'invoice')->count(),
                    'validated' => DB::table('documents')->where('is_validated', true)->count(),
                ],
                'storage' => [
                    'database_size' => $this->getDatabaseSize(),
                ],
            ],
        ]);
    }

    /**
     * Measure response time of a callback.
     */
    protected function measureResponseTime(callable $callback): string
    {
        $start = microtime(true);
        $callback();
        $end = microtime(true);

        return round(($end - $start) * 1000, 2) . 'ms';
    }

    /**
     * Get database size.
     */
    protected function getDatabaseSize(): string
    {
        try {
            $result = DB::select("SELECT pg_database_size(?) as size", [
                config('database.connections.pgsql.database')
            ]);

            $bytes = $result[0]->size ?? 0;
            return $this->formatBytes($bytes);
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Format bytes to human readable.
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
