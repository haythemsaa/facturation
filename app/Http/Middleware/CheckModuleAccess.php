<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $module  The module to check (stock, crm, hr)
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();

        if (!$user || !$user->tenant) {
            return response()->json([
                'message' => 'Tenant non trouvé.',
            ], 403);
        }

        $tenant = $user->tenant;

        // Vérifier si le tenant a une subscription active
        $activeSubscription = $tenant->activeSubscription;

        if (!$activeSubscription) {
            return response()->json([
                'message' => 'Aucun abonnement actif.',
            ], 403);
        }

        // Vérifier si le module est inclus dans l'abonnement
        if (!$activeSubscription->hasModule($module)) {
            return response()->json([
                'message' => "Vous n'avez pas accès au module {$module}. Veuillez mettre à niveau votre abonnement.",
                'current_plan' => $activeSubscription->plan,
                'available_modules' => $activeSubscription->modules,
            ], 403);
        }

        return $next($request);
    }
}
