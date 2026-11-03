<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Espace call center en français : messages de validation (lang/fr) et dates relatives Carbon.
 * L'administration garde la langue configurée dans config/app.php.
 */
class SetCallCenterLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale('fr');
        Carbon::setLocale('fr');

        return $next($request);
    }
}
