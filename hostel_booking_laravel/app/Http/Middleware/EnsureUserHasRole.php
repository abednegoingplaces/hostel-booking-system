<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Role middleware. Usage in routes: ->middleware('role:warden') or ->middleware('role:student').
 * Registered as the "role" alias in bootstrap/app.php.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if ($request->user()?->role !== $role) {
            abort(403, 'You do not have permission to view this page.');
        }

        return $next($request);
    }
}
