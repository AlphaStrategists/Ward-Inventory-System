<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRolePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $permission
     */
        public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        if ($permission) {
            $user = $request->user();
            $hasPermission = $user && $user->role
                ? $user->role->permissions()->where('permission_name', $permission)->exists()
                : false;

            if (!$hasPermission) {
                abort(403, 'Unauthorized ward inventory action.');
            }
        }

        return $next($request);
    }
}
