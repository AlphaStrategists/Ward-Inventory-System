<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public const VALID_ROLES = ['Doctor', 'Nurse', 'Pharmacist', 'Head Nurse'];

    /**
     * Allow the request through only if the logged-in user's role matches
     * one of the roles passed to the middleware, e.g.:
     *   Route::middleware(['role:Nurse'])->group(...)
     *   Route::middleware(['role:Nurse,Doctor'])->group(...)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Flatten any comma-separated role arguments, e.g. "Nurse,Doctor"
        $parsedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $r) {
                $trimmed = trim($r);
                if ($trimmed !== '') {
                    $parsedRoles[] = $trimmed;
                }
            }
        }

        if (!$user || !$user->role) {
            abort(403, 'Your role does not have permission to perform this action.');
        }

        $userRole = strtolower($user->role);
        $allowedRoles = array_map('strtolower', $parsedRoles);

        if (in_array('nurse', $allowedRoles, true) && !in_array('head nurse', $allowedRoles, true)) {
            $allowedRoles[] = 'head nurse';
        }

        if (!in_array($userRole, $allowedRoles, true)) {
            abort(403, 'Your role does not have permission to perform this action.');
        }

        return $next($request);
    }
}