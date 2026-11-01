<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Unit;
use App\Models\MedicineForm;
use App\Models\Role;
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

        // 4. Roles (Ensuring 4 canonical roles exist)
        $adminRole = Role::firstOrCreate(['role_name' => 'Admin']);
        $doctorRole = Role::firstOrCreate(['role_name' => 'Doctor']);
        $nurseRole = Role::firstOrCreate(['role_name' => 'Nurse']);
        $pharmacistRole = Role::firstOrCreate(['role_name' => 'Pharmacist']);

        // 5. Default Ward
        $wardId = DB::table('wards')->value('id');
        if (!$wardId) {
            $wardId = DB::table('wards')->insertGetId([
                'ward_number' => 'W-01',
                'ward_name' => 'Main Ward',
            ]);
        }

        // 6. Default Test Users for the 4 canonical roles
        $users = [
            [
                'email' => 'admin@test.com',
                'name' => 'System Admin',
                'role_id' => $adminRole->id,
            ],
            [
                'email' => 'doctor@test.com',
                'name' => 'Test Doctor',
                'role_id' => $doctorRole->id,
            ],
            [
                'email' => 'nurse@test.com',
                'name' => 'Test Nurse',
                'role_id' => $nurseRole->id,
            ],
            [
                'email' => 'pharmacist@test.com',
                'name' => 'Test Pharmacist',
                'role_id' => $pharmacistRole->id,
            ],
        ];

        foreach ($users as $userData) {
            DB::table('users')->updateOrInsert(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'role_id' => $userData['role_id'],
                    'ward_id' => $wardId,
                    'created_at' => now(),
                ]
            );
        }
    }
}
