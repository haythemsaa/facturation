<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantStatus
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

        // Vérifier si le tenant est actif
        if (!$tenant->is_active) {
            return response()->json([
                'message' => 'Votre compte entreprise est désactivé. Veuillez contacter le support.',
                'tenant' => $tenant->name,
            ], 403);
        }

        return $next($request);
    }
}
