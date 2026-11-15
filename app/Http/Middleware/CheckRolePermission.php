<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Role;

class CheckRolePermission
{
    public function handle(
        Request $request,
        Closure $next,
        ?string $permission = null
    ): Response {

        if (!$permission) {
            return $next($request);
        }

        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        // Admin accounts have administrative access.
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Read the role directly from users.role.
        $userRole = trim((string) $user->role);

        if ($userRole === '') {
            abort(403, 'User role could not be determined.');
        }

        // Doctor and MS represent the same application role.
        if (strcasecmp($userRole, 'Doctor') === 0) {
            $userRole = 'MS';
        }

        // Find the corresponding role record.
        $role = Role::whereRaw(
            'LOWER(TRIM(role_name)) = ?',
            [strtolower($userRole)]
        )->first();

        if (!$role) {
            abort(403, 'User role is not configured for permissions.');
        }

        // Check the requested permission.
        $hasPermission = $role->permissions()
            ->where('permission_name', $permission)
            ->exists();

        if (!$hasPermission) {
            abort(403, 'Unauthorized ward inventory action.');
        }

        return $next($request);
    }
}
