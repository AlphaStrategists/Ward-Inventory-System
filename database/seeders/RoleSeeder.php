<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates only the 4 canonical roles.
     */
    public function run(): void
    {
        $roles = [
            'Admin',
            'Doctor',
            'Nurse',
            'Pharmacist',
        ];

        $roleModels = [];
        foreach ($roles as $roleName) {
            $roleModels[$roleName] = Role::firstOrCreate(['role_name' => $roleName]);
        }

        // Reassign any existing users on non-canonical roles to canonical roles before cleanup
        $nurseRoles = Role::whereIn('role_name', ['Staff Nurse', 'Ward Sister', 'Test Nurse', 'Audit Nurse', 'Ctrl Nurse'])->pluck('id');
        if ($nurseRoles->isNotEmpty()) {
            DB::table('users')->whereIn('role_id', $nurseRoles)->update(['role_id' => $roleModels['Nurse']->id]);
        }

        $doctorRoles = Role::whereIn('role_name', ['Medical Officer', 'Test Doctor'])->pluck('id');
        if ($doctorRoles->isNotEmpty()) {
            DB::table('users')->whereIn('role_id', $doctorRoles)->update(['role_id' => $roleModels['Doctor']->id]);
        }

        $adminRoles = Role::whereIn('role_name', ['System Admin', 'MS'])->pluck('id');
        if ($adminRoles->isNotEmpty()) {
            DB::table('users')->whereIn('role_id', $adminRoles)->update(['role_id' => $roleModels['Admin']->id]);
        }

        $pharmacistRoles = Role::whereIn('role_name', ['Test Pharmacist'])->pluck('id');
        if ($pharmacistRoles->isNotEmpty()) {
            DB::table('users')->whereIn('role_id', $pharmacistRoles)->update(['role_id' => $roleModels['Pharmacist']->id]);
        }

        $canonicalIds = array_map(fn($r) => $r->id, $roleModels);
        DB::table('users')->whereNotIn('role_id', $canonicalIds)->update(['role_id' => $roleModels['Nurse']->id]);

        // Clean up role_permissions for non-canonical roles
        DB::table('role_permissions')->whereNotIn('role_id', $canonicalIds)->delete();

        // Remove any roles that are not part of the 4 canonical roles
        Role::whereNotIn('role_name', $roles)->delete();
    }
}
