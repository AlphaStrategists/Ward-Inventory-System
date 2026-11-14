<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(
        Request $request,
        Closure $next,
        ...$roles
    ): Response {

        // 1. User must be logged in
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // 2. Admin flag takes priority over the stored role
        if ($user->is_admin) {
            $request->attributes->set('active_role', 'admin');

            return $next($request);
        }

        // 3. Get the original database role
        $originalRole = $user->role;

        if (
            $originalRole === null ||
            trim((string) $originalRole) === ''
        ) {
            abort(403, 'Authenticated user role could not be determined.');
        }

        $originalRole = strtolower(trim((string) $originalRole));

        // 4. Map database roles to application roles
        $roleMap = [
            'doctor'      => 'ms',
            'ms'          => 'ms',
            'nurse'       => 'nurse',
            'pharmacist'  => 'pharmacist',
            'head nurse'  => 'head_nurse',
            'head_nurse'  => 'head_nurse',
        ];

        $userRole = $roleMap[$originalRole] ?? null;

        if ($userRole === null) {
            abort(
                403,
                'Authenticated user role "' .
                $originalRole .
                '" is not recognized by the system.'
            );
        }

        // 5. Normalize allowed middleware roles
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

        // 6. Head Nurse can access Nurse routes
        if (
            $userRole === 'head_nurse' &&
            in_array('nurse', $allowedRoles, true)
        ) {
            $request->attributes->set('active_role', $userRole);

            return $next($request);
        }

        // 7. Check permissions
        if (!in_array($userRole, $allowedRoles, true)) {
            abort(
                403,
                'Unauthorized. Your role is "' .
                $userRole .
                '". Required role: ' .
                implode(', ', $allowedRoles)
            );
        }

        $request->attributes->set('active_role', $userRole);

        return $next($request);
    }
}
