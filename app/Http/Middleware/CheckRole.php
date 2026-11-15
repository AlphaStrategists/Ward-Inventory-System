<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Resolve the user's role from the admin flag, role text, or role_id.
        // Existing users may have NULL in the `role` column.
        if ((bool) $user->is_admin) {
            $userRole = 'admin';
        } else {
            $originalRole = strtolower(trim((string) ($user->role ?? '')));

            $roleMap = [
                'admin' => 'admin',
                'doctor' => 'ms',
                'ms' => 'ms',
                'nurse' => 'nurse',
                'pharmacist' => 'pharmacist',
                'head nurse' => 'head_nurse',
                'head_nurse' => 'head_nurse',
                'headnurse' => 'head_nurse',
            ];

            $userRole = $roleMap[$originalRole] ?? null;

            if ($userRole === null) {
                $roleIdMap = [
                    1 => 'admin',
                    2 => 'ms',
                    3 => 'nurse',
                    4 => 'pharmacist',
                ];

                $userRole = $roleIdMap[(int) ($user->role_id ?? 0)] ?? null;
            }
        }

        if ($userRole === null) {
            abort(403, 'Authenticated user role could not be determined.');
        }

        $allowedRoles = [];

        foreach ($roles as $role) {
            foreach (explode(',', $role) as $allowedRole) {
                $allowedRole = strtolower(trim($allowedRole));

                $allowedRole = match ($allowedRole) {
                    'doctor' => 'ms',
                    'head nurse', 'headnurse' => 'head_nurse',
                    default => $allowedRole,
                };

                if ($allowedRole !== '') {
                    $allowedRoles[] = $allowedRole;
                }
            }
        }

        // Head Nurse inherits Nurse access only when Nurse is explicitly allowed.
        $isAllowed = in_array($userRole, $allowedRoles, true)
            || ($userRole === 'head_nurse' && in_array('nurse', $allowedRoles, true));

        if (!$isAllowed) {
            abort(
                403,
                'Unauthorized. Your role is "' . $userRole .
                '". Required role: ' . implode(', ', $allowedRoles)
            );
        }

        $request->attributes->set('active_role', $userRole);

        return $next($request);
    }
}
