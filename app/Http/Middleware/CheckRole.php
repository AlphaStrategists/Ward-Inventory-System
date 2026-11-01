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

        /*
        |--------------------------------------------------------------------------
        | 1. User must be logged in
        |--------------------------------------------------------------------------
        */
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Get the user's ORIGINAL database role
        |--------------------------------------------------------------------------
        |
        | The User model supports both:
        |   - users.role
        |   - users.role_id -> roles.role_name
        |
        */
        $originalRole = $user->role;

        if (
            $originalRole === null ||
            trim((string) $originalRole) === ''
        ) {
            abort(
                403,
                'Authenticated user role could not be determined.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Normalize original database role
        |--------------------------------------------------------------------------
        */
        $originalRole = strtolower(
            trim((string) $originalRole)
        );

        /*
        |--------------------------------------------------------------------------
        | 4. Convert ORIGINAL roles to SURGICAL roles
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | We are NOT changing the database role.
        |
        | Doctor -> MS
        | Nurse -> Nurse
        | Pharmacist -> Pharmacist
        | Admin -> Admin
        | Head Nurse -> Head Nurse
        |
        */
        $roleMap = [
            'doctor'      => 'ms',
            'nurse'       => 'nurse',
            'pharmacist'  => 'pharmacist',
            'admin'       => 'admin',
            'head nurse'  => 'head_nurse',
            'head_nurse'  => 'head_nurse',
        ];

        $userRole = $roleMap[$originalRole] ?? null;

        /*
        |--------------------------------------------------------------------------
        | 5. Unknown role
        |--------------------------------------------------------------------------
        */
        if ($userRole === null) {
            abort(
                403,
                'Authenticated user role "' .
                $originalRole .
                '" is not recognized by the system.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 6. Convert middleware roles
        |--------------------------------------------------------------------------
        |
        | Example:
        | role:ms
        | role:nurse,admin
        |
        */
        $allowedRoles = [];

        foreach ($roles as $role) {

            foreach (explode(',', $role) as $allowedRole) {

                $allowedRole = strtolower(
                    trim((string) $allowedRole)
                );

                if ($allowedRole === '') {
                    continue;
                }

                /*
                | Doctor can also be used as MS if needed.
                */
                if ($allowedRole === 'doctor') {
                    $allowedRole = 'ms';
                }

                $allowedRoles[] = $allowedRole;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 7. Head Nurse has Nurse permissions
        |--------------------------------------------------------------------------
        */
        if (
            $userRole === 'head_nurse' &&
            in_array('nurse', $allowedRoles, true)
        ) {
            $request->attributes->set('active_role', $userRole);

            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | 8. Check permission
        |--------------------------------------------------------------------------
        */
        if (!in_array($userRole, $allowedRoles, true)) {
            abort(
                403,
                'Unauthorized. Your role is "' .
                $userRole .
                '". Required role: ' .
                implode(', ', $allowedRoles)
            );
        }

        // Make the mapped role available to controllers.
        $request->attributes->set('active_role', $userRole);

        return $next($request);
    }
}