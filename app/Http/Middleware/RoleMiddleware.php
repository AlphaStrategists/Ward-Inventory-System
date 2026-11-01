<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        /*
        |--------------------------------------------------------------------------
        | Get the actual role from the database
        |--------------------------------------------------------------------------
        */

        $databaseRole = DB::table('roles')
            ->where('id', $user->role_id)
            ->value('role_name');

        if (!$databaseRole) {
            return response()->json([
                'success' => false,
                'message' => 'User role could not be determined.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Database role -> Application role
        |--------------------------------------------------------------------------
        */

        $roleMap = [
            'Admin'       => 'admin',
            'Doctor'      => 'ms',
            'Nurse'       => 'nurse',
            'Pharmacist'  => 'pharmacist',
        ];

        $activeRole = $roleMap[$databaseRole] ?? null;

        if (!$activeRole) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid user role.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Save the application role for the current request
        |--------------------------------------------------------------------------
        */

        $request->attributes->set('active_role', $activeRole);

        /*
        |--------------------------------------------------------------------------
        | Admin can access all protected areas
        |--------------------------------------------------------------------------
        */

        if ($activeRole === 'admin') {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Check requested role
        |--------------------------------------------------------------------------
        */

        $allowedRoles = array_map(
            fn ($role) => strtolower(trim($role)),
            $roles
        );

        if (in_array($activeRole, $allowedRoles, true)) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'You do not have permission to access this page.',
        ], 403);
    }
}