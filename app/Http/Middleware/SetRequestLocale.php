<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetRequestLocale
{
    private const SUPPORTED_LOCALES = ['en', 'sw'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        app()->setLocale($locale);
        $request->setLocale($locale);

        $response = $next($request);
        $response->headers->set('Content-Language', $locale);

        return $response;
    }

    private function resolveLocale(Request $request): string
    {
        $candidates = array_filter([
            $request->header('X-Locale'),
            $request->getPreferredLanguage(self::SUPPORTED_LOCALES),
        ]);

        foreach ($candidates as $candidate) {
            $locale = strtolower((string) strtok((string) $candidate, '-_'));

            if (in_array($locale, self::SUPPORTED_LOCALES, true)) {
                return $locale;
            }
        }

        return config('app.locale', 'en');
    }
}
