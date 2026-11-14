<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
        ...$roles
    ): Response {

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Use the central role identification from User.php.
        if ($user->isAdmin()) {
            $request->attributes->set('active_role', 'admin');
            return $next($request);
        }

        $databaseRole = trim((string) $user->role);

        if ($databaseRole === '') {
            return response()->json([
                'success' => false,
                'message' => 'User role could not be determined.',
            ], 403);
        }

        $roleMap = [
            'doctor' => 'ms',
            'ms' => 'ms',
            'nurse' => 'nurse',
            'head nurse' => 'head_nurse',
            'head_nurse' => 'head_nurse',
            'pharmacist' => 'pharmacist',
        ];

        $normalisedRole = strtolower(
            str_replace('_', ' ', $databaseRole)
        );

        $activeRole = $roleMap[$normalisedRole] ?? null;

        if (!$activeRole) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid user role: ' . $databaseRole,
            ], 403);
        }

        $allowedRoles = [];

        foreach ($roles as $role) {
            foreach (explode(',', $role) as $allowedRole) {
                $allowedRole = strtolower(trim($allowedRole));

                if ($allowedRole === 'doctor') {
                    $allowedRole = 'ms';
                }

                if ($allowedRole !== '') {
                    $allowedRoles[] = $allowedRole;
                }
            }
        }

        // Head Nurse can access Nurse-level routes.
        if (
            $activeRole === 'head_nurse' &&
            in_array('nurse', $allowedRoles, true)
        ) {
            $request->attributes->set('active_role', $activeRole);
            return $next($request);
        }

        if (!in_array($activeRole, $allowedRoles, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Your role is "' .
                    $activeRole . '". Required role: ' .
                    implode(', ', $allowedRoles),
            ], 403);
        }

        $request->attributes->set('active_role', $activeRole);

        return $next($request);
    }
}
