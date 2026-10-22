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
     * a dummy user on the fly if one does not exist yet.
     *
     * Usage:  /test-login/Admin
     *         /test-login/Doctor
     *         /test-login/Nurse
     *         /test-login/Pharmacist
     *
     * Delete this controller and its route before shipping to production.
     */
    public function login(string $role): RedirectResponse
    {
        // Hard safety net — never runs outside local/testing, even if
        // someone forgets to remove the route later.
        abort_unless(app()->environment(['local', 'testing']), 404);

        $allowedRoles = ['Admin', 'Doctor', 'Nurse', 'Pharmacist'];
        $roleName = null;
        foreach ($allowedRoles as $allowed) {
            if (strcasecmp($role, $allowed) === 0) {
                $roleName = $allowed;
                break;
            }
        }

        if (!$roleName) {
            abort(404, "Invalid role. Only 'Admin', 'Doctor', 'Nurse', and 'Pharmacist' are permitted.");
        }

        // Look for the role itself.
        $roleModel = Role::where('role_name', $roleName)->first();
        if (!$roleModel) {
            $roleModel = Role::create(['role_name' => $roleName]);
        }

        // Search for an existing user already assigned this role.
        $user = User::where('role_id', $roleModel->id)->first();

        if (!$user) {
            // None found — create a dummy user on the fly.
            $user = User::create([
                'name' => $roleName . ' (Test User)',
                'email' => strtolower($roleName) . '.test@hospital.gov.lk',
                'password' => Hash::make('password123'),
                'role_id' => $roleModel->id,
            ]);
        }

        // Log them in.
        Auth::login($user);

        // Redirect to the app's general inventory or dashboard route.
        return redirect()->route('general-inventory.index')
            ->with('success', "Test-logged in as: {$roleName}");
    }
}