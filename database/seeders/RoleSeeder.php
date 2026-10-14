<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Admin',
            'MS',
            'Medical Officer',
            'Ward Sister',
            'Staff Nurse',
            'Pharmacist'
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['role_name' => $roleName]);
        }

        // Assign permissions
        $rolePermissions = [
            'Staff Nurse' => ['manage_inventory', 'manage_admissions', 'dispense_narcotics', 'dispense_medicine', 'view_inventory'],
            'Ward Sister' => ['manage_inventory', 'manage_admissions', 'dispense_narcotics', 'dispense_medicine', 'view_inventory'],
            'Medical Officer' => ['witness_narcotics', 'view_inventory'],
            'Pharmacist' => ['add_medicine', 'manage_medicines', 'add_stock', 'view_inventory'],
            'MS' => ['view_inventory'],
        ];

        $allPermissions = Permission::pluck('id', 'permission_name')->toArray();
        $adminRole = Role::where('role_name', 'Admin')->first();

        // Admin gets all permissions
        foreach ($allPermissions as $permName => $permId) {
            if (!DB::table('role_permissions')->where('role_id', $adminRole->id)->where('permission_id', $permId)->exists()) {
                DB::table('role_permissions')->insert([
                    'role_id' => $adminRole->id,
                    'permission_id' => $permId,
                    'created_at' => now(),
                ]);
            }
        }

        // Assign specific permissions to roles
        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::where('role_name', $roleName)->first();
            if (!$role) continue;
            
            foreach ($perms as $permName) {
                if (isset($allPermissions[$permName])) {
                    $permId = $allPermissions[$permName];
                    if (!DB::table('role_permissions')->where('role_id', $role->id)->where('permission_id', $permId)->exists()) {
                        DB::table('role_permissions')->insert([
                            'role_id' => $role->id,
                            'permission_id' => $permId,
                            'created_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}
