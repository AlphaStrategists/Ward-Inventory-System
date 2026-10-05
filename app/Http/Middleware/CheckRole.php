<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Allow the request through only if the logged-in user's role_name
     * matches one of the roles passed to the middleware, e.g.:
     *   Route::middleware(['role:Staff Nurse'])->group(...)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !$user->role || !in_array($user->role->role_name, $roles, true)) {
            abort(403, 'Your role does not have permission to perform this action.');
        }

        return $next($request);
    }
}