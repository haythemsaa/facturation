<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->tenant) {
            return response()->json([
                'message' => 'Tenant non trouvé.',
            ], 403);
        }

        $tenant = $user->tenant;
        $activeSubscription = $tenant->activeSubscription;

        if (!$activeSubscription) {
            return response()->json([
                'message' => 'Aucun abonnement actif. Veuillez souscrire à un plan.',
            ], 403);
        }

        // Vérifier si l'abonnement est actif
        if (!$activeSubscription->isActive()) {
            return response()->json([
                'message' => 'Votre abonnement n\'est pas actif. Statut actuel: ' . $activeSubscription->status,
                'status' => $activeSubscription->status,
                'ends_at' => $activeSubscription->ends_at?->toDateString(),
            ], 403);
        }

        // Vérifier si l'abonnement est expiré
        if ($activeSubscription->ends_at && $activeSubscription->ends_at->isPast()) {
            return response()->json([
                'message' => 'Votre abonnement a expiré le ' . $activeSubscription->ends_at->format('d/m/Y'),
                'ends_at' => $activeSubscription->ends_at->toDateString(),
            ], 403);
        }

        return $next($request);
    }
}
