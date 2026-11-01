<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'view_inventory',
            'manage_inventory',
            'add_medicine',
            'manage_medicines',
            'add_stock',
            'dispense_medicine',
            'dispense_narcotics',
            'witness_narcotics',
            'manage_admissions',
        ];

        $permissionModels = [];
        foreach ($permissions as $permName) {
            $permissionModels[$permName] = Permission::firstOrCreate(['permission_name' => $permName]);
        }

        // Consolidated permissions for the 4 canonical roles
        $rolePermissions = [
            'Admin' => [
                'view_inventory',
                'manage_inventory',
                'add_medicine',
                'manage_medicines',
                'add_stock',
                'dispense_medicine',
                'dispense_narcotics',
                'witness_narcotics',
                'manage_admissions',
            ],
            'Nurse' => [
                'view_inventory',
                'manage_inventory',
                'add_stock',
                'dispense_medicine',
                'dispense_narcotics',
                'witness_narcotics',
                'manage_admissions',
            ],
            'Pharmacist' => [
                'view_inventory',
                'add_medicine',
                'manage_medicines',
                'add_stock',
            ],
            'Doctor' => [
                'view_inventory',
                'witness_narcotics',
                'manage_admissions',
                'dispense_medicine',
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::where('role_name', $roleName)->first();
            if ($role) {
                $ids = array_map(fn($p) => $permissionModels[$p]->id, $perms);
                $role->permissions()->sync($ids);
            }
        }
    }
}
