<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public const NURSE_ROLES = ['Nurse', 'Staff Nurse', 'Ward Sister'];

    /**
     * Allow the request through only if the logged-in user's role_name
     * matches one of the roles passed to the middleware, e.g.:
     *   Route::middleware(['role:Staff Nurse'])->group(...)
     *   Route::middleware(['role:Nurse,Staff Nurse,Ward Sister'])->group(...)
     *
     * Note: If any nursing role ('Nurse', 'Staff Nurse', 'Ward Sister') is checked,
     * all equivalent nursing roles are automatically permitted.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Flatten any comma-separated role arguments, e.g. "Nurse,Staff Nurse"
        $parsedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $r) {
                $trimmed = trim($r);
                if ($trimmed !== '') {
                    $parsedRoles[] = $trimmed;
                }
            }
        }

        // If any nurse role is requested, expand to allow all equivalent nurse roles
        if (array_intersect($parsedRoles, self::NURSE_ROLES)) {
            $parsedRoles = array_unique(array_merge($parsedRoles, self::NURSE_ROLES));
        }

        if (!$user || !$user->role || !in_array($user->role, $parsedRoles, true)) {
            abort(403, 'Your role does not have permission to perform this action.');
        }

        return $next($request);
    }
}