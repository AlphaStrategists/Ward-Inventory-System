<?php

namespace DatabaseSeeders;

// Note: standard Laravel seeder class is Database\Seeders\DatabaseSeeder or DatabaseSeeder depending on namespace
namespace Database\Seeders;


use App\Models\Category;
use App\Models\Medicine;
use App\Models\MedicineForm;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $nurseRole = Role::firstOrCreate(['role_name' => 'Ward Nurse']);
        $msRole = Role::firstOrCreate(['role_name' => 'MS Officer']);
        $headNurseRole = Role::firstOrCreate(['role_name' => 'Head Nurse']);

        // 2. Ward
        $ward = Ward::firstOrCreate([
            'ward_number' => '48',
            'ward_name' => 'Ward 48 - General Ward'
        ]);

        // 3. Users
        $nurse = User::firstOrCreate(
            ['email' => 'nurse@ward48.com'],
            [
                'name' => 'Ward Nurse',
                'password' => Hash::make('password'),
                'role_id' => $nurseRole->id,
                'ward_id' => $ward->id,
            ]
        );

        $msOfficer = User::firstOrCreate(
            ['email' => 'ms@hospital.com'],
            [
                'name' => 'Medical Superintendent (MS)',
                'password' => Hash::make('password'),
                'role_id' => $msRole->id,
                'ward_id' => $ward->id,
            ]
        );

        // 4. Categories
        $categoriesList = [
            'Injection' => 'Injection medicines',
            'Injection Antibiotics' => 'Antibiotic injections',
            'Narcotic' => 'Narcotic controlled drugs',
            'Syrup' => 'Syrups and oral liquids',
            'IV fluid' => 'Intravenous fluids',
            'Oral countable' => 'Countable oral tablets/capsules',
            'Oral antibiotics' => 'Oral antibiotic medications',
            'Bulk medicine' => 'Bulk supply medications',
            'Surgical consumable 1' => 'Surgical items type 1',
            'Surgical consumable 2' => 'Surgical items type 2',
            'Local purchase' => 'Locally purchased items'
        ];

        foreach ($categoriesList as $catName => $catDesc) {
            Category::firstOrCreate(['name' => $catName], ['description' => $catDesc]);
        }

        $injCategory = Category::where('name', 'Injection')->first();
        $injAntiCategory = Category::where('name', 'Injection Antibiotics')->first();

        // 5. Units
        $unitsList = ['ml', 'mg', 'g', 'vial', 'ampoule', 'tablet', 'bottle'];
        foreach ($unitsList as $uName) {
            Unit::firstOrCreate(['unit_name' => $uName]);
        }
        $mlUnit = Unit::where('unit_name', 'ml')->first();
        $gUnit = Unit::where('unit_name', 'g')->first();
        $mgUnit = Unit::where('unit_name', 'mg')->first();

        // 6. Medicine Forms
        $formsList = ['Injection', 'Vial', 'Ampoule', 'Syrup', 'Tablet', 'Infusion'];
        foreach ($formsList as $fName) {
            MedicineForm::firstOrCreate(['form_name' => $fName]);
        }
        $injForm = MedicineForm::where('form_name', 'Injection')->first();
        $vialForm = MedicineForm::where('form_name', 'Vial')->first();

        // 7. Initial Medicines for Injection
        $adrenalin = Medicine::firstOrCreate(
            ['item_code' => 'MED-INJ-001'],
            [
                'name' => 'Adrenalin',
                'category_id' => $injCategory->id,
                'unit_id' => $mlUnit->id,
                'form_id' => $injForm->id,
                'strength' => '1 mg/ml',
                'is_controlled' => 0,
                'min_level' => 30,
                'warning_limit' => 50,
            ]
        );

        $aminophilin = Medicine::firstOrCreate(
            ['item_code' => 'MED-INJ-002'],
            [
                'name' => 'AMINOPHILIN',
                'category_id' => $injCategory->id,
                'unit_id' => $mlUnit->id,
                'form_id' => $injForm->id,
                'strength' => '250 mg/10ml',
                'is_controlled' => 0,
                'min_level' => 250,
                'warning_limit' => 300,
            ]
        );

        $domparatin = Medicine::firstOrCreate(
            ['item_code' => 'MED-INJ-003'],
            [
                'name' => 'domparatin',
                'category_id' => $injCategory->id,
                'unit_id' => $mlUnit->id,
                'form_id' => $injForm->id,
                'strength' => '10 mg/2ml',
                'is_controlled' => 0,
                'min_level' => 50,
                'warning_limit' => 80,
            ]
        );

        // Medicines for Injection Antibiotics
        Medicine::firstOrCreate(
            ['item_code' => 'MED-ANT-001'],
            [
                'name' => 'Ceftriaxone',
                'category_id' => $injAntiCategory->id,
                'unit_id' => $gUnit->id,
                'form_id' => $vialForm->id,
                'strength' => '1 g',
                'is_controlled' => 0,
                'min_level' => 100,
                'warning_limit' => 150,
            ]
        );

        Medicine::firstOrCreate(
            ['item_code' => 'MED-ANT-002'],
            [
                'name' => 'Meropenem',
                'category_id' => $injAntiCategory->id,
                'unit_id' => $mgUnit->id,
                'form_id' => $vialForm->id,
                'strength' => '500 mg',
                'is_controlled' => 0,
                'min_level' => 40,
                'warning_limit' => 80,
            ]
        );

        // 8. Sample Orders matching user screenshot
        $sampleOrdersData = [
            ['med' => $adrenalin, 'qty' => 30, 'date' => '2026-09-16 10:00:00', 'status' => 'Pending'],
            ['med' => $domparatin, 'qty' => 50, 'date' => '2026-09-16 11:30:00', 'status' => 'Pending'],
            ['med' => $adrenalin, 'qty' => 50, 'date' => '2026-09-16 14:15:00', 'status' => 'Pending'],
        ];

        foreach ($sampleOrdersData as $idx => $sData) {
            $reqNo = 'REQ-20260916-00' . ($idx + 1);
            $order = Order::firstOrCreate(
                ['req_no' => $reqNo],
                [
                    'ward_id' => $ward->id,
                    'date' => $sData['date'],
                    'requested_by' => $nurse->id,
                    'ms_approval_status' => $sData['status'],
                    'approved_by' => null,
                    'remark' => 'Urgent ward requisition'
                ]
            );

            OrderDetail::firstOrCreate(
                [
                    'order_id' => $order->id,
                    'medicine_id' => $sData['med']->id
                ],
                [
                    'qty_requested' => $sData['qty'],
                    'qty_issued' => 0,
                    'remark' => 'Standard dose requisition'
                ]
            );
        }
    }
}
