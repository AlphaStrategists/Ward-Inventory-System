<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(
        Request $request,
        Closure $next,
        ...$roles
    ): Response {
        /*
        |--------------------------------------------------------------------------
        | 1. Check authentication
        |--------------------------------------------------------------------------
        */

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }


        /*
        |--------------------------------------------------------------------------
        | 2. Normalize allowed roles from the route
        |--------------------------------------------------------------------------
        |
        | Example:
        | role:nurse,admin
        |
        */

        $allowedRoles = array_map(
            fn ($role) => strtolower(trim((string) $role)),
            $roles
        );


        /*
        |--------------------------------------------------------------------------
        | 3. Get the authenticated user's role from the database
        |--------------------------------------------------------------------------
        |
        | We do NOT trust:
        | - URL role
        | - X-Role header
        | - request input
        |
        | The role comes from:
        |
        | users.role_id
        |        ↓
        | roles.id
        |        ↓
        | roles.role_name
        |
        */

        $userRole = DB::table('roles')
            ->where('id', $user->role_id)
            ->value('role_name');


        /*
        |--------------------------------------------------------------------------
        | 4. Make sure the user's role exists
        |--------------------------------------------------------------------------
        */

        if (!$userRole) {
            return response()->json([
                'success' => false,
                'message' => 'User role could not be determined.',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | 5. Normalize the user's role
        |--------------------------------------------------------------------------
        */

        $userRole = strtolower(
            trim((string) $userRole)
        );


        /*
        |--------------------------------------------------------------------------
        | 6. Store the authenticated user's real role
        |--------------------------------------------------------------------------
        |
        | Controllers can retrieve it using:
        |
        | $request->attributes->get('active_role');
        |
        */

        $request->attributes->set(
            'active_role',
            $userRole
        );


        /*
        |--------------------------------------------------------------------------
        | 7. Admin has access
        |--------------------------------------------------------------------------
        |
        | Admin can access all role-protected areas.
        |
        */

        if ($userRole === 'admin') {
            return $next($request);
        }


        /*
        |--------------------------------------------------------------------------
        | 8. Check whether the authenticated user's role is allowed
        |--------------------------------------------------------------------------
        */

        if (in_array($userRole, $allowedRoles, true)) {
            return $next($request);
        }


        /*
        |--------------------------------------------------------------------------
        | 9. User does not have permission
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => false,
            'message' => 'You do not have permission to access this page.',
        ], 403);
    }
}