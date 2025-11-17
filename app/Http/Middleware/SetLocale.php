<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->detectLocale($request);

        App::setLocale($locale);

        return $next($request);
    }

    /**
     * Detect locale from multiple sources.
     */
    protected function detectLocale(Request $request): string
    {
        // 1. Check URL parameter (?lang=ar)
        if ($request->has('lang')) {
            $lang = $request->get('lang');
            if ($this->isValidLocale($lang)) {
                return $lang;
            }
        }

        // 2. Check Accept-Language header
        if ($request->hasHeader('Accept-Language')) {
            $header = $request->header('Accept-Language');
            $lang = substr($header, 0, 2); // Extract first 2 chars
            if ($this->isValidLocale($lang)) {
                return $lang;
            }
        }

        // 3. Check user preference
        if ($user = $request->user()) {
            if ($user->tenant && $user->tenant->settings) {
                $settings = $user->tenant->settings;
                if ($this->isValidLocale($settings->language)) {
                    return $settings->language;
                }
            }
        }

        // 4. Check tenant settings
        if ($user = $request->user()) {
            if ($user->tenant) {
                $settings = \App\Models\TenantSettings::forTenant($user->tenant_id);
                if ($this->isValidLocale($settings->language)) {
                    return $settings->language;
                }
            }
        }

        // 5. Default to French
        return config('app.locale', 'fr');
    }

    /**
     * Check if locale is valid.
     */
    protected function isValidLocale(string $locale): bool
    {
        return in_array($locale, ['fr', 'ar', 'en']);
    }
}
