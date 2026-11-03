<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applique la langue choisie par l'utilisateur (français, anglais ou arabe), mémorisée en session.
 * Sans choix : la langue par défaut de l'application (APP_LOCALE).
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $available = array_keys(config('app.available_locales', []));
        $locale = $request->session()->get('locale');

        if (! in_array($locale, $available, true)) {
            $locale = config('app.locale');
        }

        if (in_array($locale, $available, true)) {
            app()->setLocale($locale);
            Carbon::setLocale($locale);
        }

        return $next($request);
    }
}
