<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimiter
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $tier = 'default'): Response
    {
        $key = $this->resolveRequestSignature($request, $tier);
        $maxAttempts = $this->getMaxAttempts($tier);
        $decayMinutes = $this->getDecayMinutes($tier);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($key);

            return response()->json([
                'message' => 'Too many requests. Please slow down.',
                'retry_after' => $retryAfter,
            ], Response::HTTP_TOO_MANY_REQUESTS)
                ->header('Retry-After', $retryAfter)
                ->header('X-RateLimit-Limit', $maxAttempts)
                ->header('X-RateLimit-Remaining', 0);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        $remaining = $maxAttempts - RateLimiter::attempts($key);

        return $response
            ->header('X-RateLimit-Limit', $maxAttempts)
            ->header('X-RateLimit-Remaining', max(0, $remaining));
    }

    /**
     * Resolve request signature for rate limiting.
     */
    protected function resolveRequestSignature(Request $request, string $tier): string
    {
        $user = $request->user();

        if ($user) {
            // Per user + tenant
            return "api:{$tier}:user:{$user->id}:tenant:{$user->tenant_id}";
        }

        // Per IP for unauthenticated requests
        return "api:{$tier}:ip:" . $request->ip();
    }

    /**
     * Get max attempts based on tier.
     */
    protected function getMaxAttempts(string $tier): int
    {
        return match($tier) {
            'strict' => 10,      // 10 requests
            'default' => 60,     // 60 requests
            'relaxed' => 200,    // 200 requests
            'unlimited' => 10000, // Pratiquement illimité
            default => 60,
        };
    }

    /**
     * Get decay time in minutes.
     */
    protected function getDecayMinutes(string $tier): int
    {
        return match($tier) {
            'strict' => 1,       // Par minute
            'default' => 1,      // Par minute
            'relaxed' => 1,      // Par minute
            'unlimited' => 1,
            default => 1,
        };
    }
}
