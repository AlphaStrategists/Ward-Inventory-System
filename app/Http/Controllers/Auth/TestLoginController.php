<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TestLoginController extends Controller
{
    /**
     * DEV-ONLY: instantly logs in as a user with the given role, creating
     * both the role and a dummy user on the fly if neither exists yet.
     *
     * Usage:  /test-login/Nurse
     *         /test-login/Admin
     *         /test-login/MS
     *
     * Delete this controller and its route before shipping to production.
     */
    public function login(string $role): RedirectResponse
    {
        // Hard safety net — never runs outside local/testing, even if
        // someone forgets to remove the route later.
        abort_unless(app()->environment(['local', 'testing']), 404);

        // "nurse" (any case/spacing) maps to the exact role name the rest
        // of the app checks against: hasRole('Staff Nurse').
        $roleName = strcasecmp($role, 'nurse') === 0 ? 'Staff Nurse' : $role;

        // Look for the role itself first.
        $roleModel = Role::where('role_name', $roleName)->first();

        if (!$roleModel) {
            // Role doesn't exist in the DB at all — create it so the
            // foreign key on users.role_id has something valid to point to.
            $roleModel = Role::create(['role_name' => $roleName]);
        }

        // Search for an existing user already assigned this role.
        $user = User::where('role_id', $roleModel->id)->first();

        if (!$user) {
            // None found — create a dummy user on the fly.
            $user = User::create([
                'name' => $roleName . ' (Test User)',
                'email' => strtolower(str_replace(' ', '.', $roleName)) . '@test.local',
                'password' => Hash::make('password'),
                'role_id' => $roleModel->id,
            ]);
        }

        // Log them in.
        Auth::login($user);

        // Redirect to the app's home/dashboard route.
        return redirect()->route('general-inventory.index')
            ->with('success', "Test-logged in as: {$roleName}");
    }
}