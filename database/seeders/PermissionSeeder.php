<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage_inventory',
            'manage_admissions',
            'dispense_narcotics',
            'dispense_medicine',
            'witness_narcotics',
            'add_medicine',
            'manage_medicines',
            'add_stock',
            'view_inventory',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['permission_name' => $perm]);
        }
    }
}
