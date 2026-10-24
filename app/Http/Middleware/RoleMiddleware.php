<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $activeRole = $this->resolveActiveRole($request);
        $allowedRoles = array_map('strtolower', $roles);
        $activeRole = strtolower($activeRole);

        // Admin has full access to all nurse, ms, and pharmacist actions
        if ($activeRole === 'admin' || in_array($activeRole, $allowedRoles, true)) {
            $request->attributes->set('active_role', $activeRole);
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => "Unauthorized access: Role '{$activeRole}' is not permitted to perform this action. Allowed: " . implode(', ', $roles) . '.'
            ], 403);
        }

        abort(403, "Unauthorized: Access restricted to " . implode(', ', $roles));
    }

    /**
     * Resolve the active role from headers, inputs, route, session, user, or referer.
     */
    private function resolveActiveRole(Request $request): string
    {
        // 1. Explicit Header
        if ($headerRole = $request->header('X-Role')) {
            return trim($headerRole);
        }

        // 2. Request body / query
        if ($inputRole = $request->input('role') ?? $request->input('active_role')) {
            return trim($inputRole);
        }

        // 3. Route parameter
        if ($routeRole = $request->route('role')) {
            return trim($routeRole);
        }

        // 4. Session
        if ($sessionRole = session('role') ?? session('active_role')) {
            return trim($sessionRole);
        }

        // 5. Authenticated user
        if ($user = $request->user()) {
            if (!empty($user->role_name)) return $user->role_name;
            if ($user->role_id === 1) return 'nurse';
        }

        // 6. Referer URL path (e.g. /nurse/..., /ms/..., /pharmacist/..., /admin/...)
        $referer = $request->header('referer');
        if ($referer && preg_match('#/(admin|nurse|ms|pharmacist)(/|\?|$)#i', $referer, $matches)) {
            return strtolower($matches[1]);
        }

        return 'nurse';
    }
}
