<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Unit;
use App\Models\MedicineForm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Categories
        $categories = ['antibiotics', 'oral-antibiotics', 'syrups', 'narcotics', 'iv-fluids', 'OralCountable'];
        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat]);
        }

        // 2. Units
        $units = ['mg', 'ml', 'g', 'Tablet', 'Capsule', 'Vial', 'Ampoule'];
        foreach ($units as $u) {
            Unit::firstOrCreate(['unit_name' => $u]);
        }

        // 3. Medicine Forms
        $forms = ['Tablet', 'Capsule', 'Syrup', 'Injection', 'Cream', 'Drops'];
        foreach ($forms as $f) {
            MedicineForm::firstOrCreate(['form_name' => $f]);
        }

        // 4. Roles
        $roles = ['Admin', 'Doctor', 'Nurse', 'Pharmacist'];
        foreach ($roles as $r) {
            DB::table('roles')->updateOrInsert(['role_name' => $r]);
        }

        // 5. Permissions
        $permissions = ['add_medicine', 'manage_medicines', 'add_stock', 'dispense_medicine'];
        foreach ($permissions as $p) {
            DB::table('permissions')->updateOrInsert(['permission_name' => $p]);
        }

        // 6. Role Mapping
        $adminRole = DB::table('roles')->where('role_name', 'Admin')->first();
        if ($adminRole) {
            foreach ($permissions as $p) {
                $permission = DB::table('permissions')->where('permission_name', $p)->first();
                if ($permission) {
                    DB::table('role_permissions')->updateOrInsert([
                        'role_id' => $adminRole->id,
                        'permission_id' => $permission->id,
                    ]);
                }
            }
        }

        // 7. Default User
        $wardId = DB::table('wards')->value('id');
        if (!$wardId) {
            $wardId = DB::table('wards')->insertGetId([
                'ward_number' => 'W-01',
                'ward_name' => 'Main Ward',
            ]);
        }

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@test.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'role_id' => $adminRole ? $adminRole->id : 1,
                'ward_id' => $wardId,
                'created_at' => now()
            ]
        );
    }
}
