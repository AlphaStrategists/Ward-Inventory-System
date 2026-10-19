<?php

namespace Database\Seeders;

use App\Models\Admission;
use App\Models\Category;
use App\Models\Dispensation;
use App\Models\GeneralItem;
use App\Models\GeneralStockAdjustment;
use App\Models\GeneralTransaction;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineForm;
use App\Models\Patient;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class WardInventorySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles & Permissions
        $adminRole = Role::firstOrCreate(['role_name' => 'Admin']);
        $sisterRole = Role::firstOrCreate(['role_name' => 'Ward Sister']);
        $nurseRole = Role::firstOrCreate(['role_name' => 'Staff Nurse']);
        $doctorRole = Role::firstOrCreate(['role_name' => 'Medical Officer']);

        $perms = [
            'view_inventory',
            'manage_inventory',
            'dispense_narcotics',
            'witness_narcotics',
            'manage_admissions',
        ];

        foreach ($perms as $permName) {
            $perm = Permission::firstOrCreate(['permission_name' => $permName]);
            if (!DB::table('role_permissions')->where('role_id', $adminRole->id)->where('permission_id', $perm->id)->exists()) {
                DB::table('role_permissions')->insert([
                    'role_id' => $adminRole->id,
                    'permission_id' => $perm->id,
                    'created_at' => now(),
                ]);
            }
            if (!DB::table('role_permissions')->where('role_id', $sisterRole->id)->where('permission_id', $perm->id)->exists()) {
                DB::table('role_permissions')->insert([
                    'role_id' => $sisterRole->id,
                    'permission_id' => $perm->id,
                    'created_at' => now(),
                ]);
            }
            if (in_array($permName, ['view_inventory', 'dispense_narcotics']) && !DB::table('role_permissions')->where('role_id', $nurseRole->id)->where('permission_id', $perm->id)->exists()) {
                DB::table('role_permissions')->insert([
                    'role_id' => $nurseRole->id,
                    'permission_id' => $perm->id,
                    'created_at' => now(),
                ]);
            }
        }

        // 2. Wards
        $ward48 = Ward::firstOrCreate(
            ['ward_number' => 'Ward 48'],
            ['ward_name' => 'Male Surgical Ward 48']
        );
        $ward49 = Ward::firstOrCreate(
            ['ward_number' => 'Ward 49'],
            ['ward_name' => 'Female Surgical Ward 49']
        );

        // 3. Categories
        $catLinen = Category::firstOrCreate(['name' => 'Linen & Bedding'], ['description' => 'Hospital ward bedding, linen, and drapery']);
        $catConsumables = Category::firstOrCreate(['name' => 'Medical Consumables'], ['description' => 'Disposables, gloves, syringes, and cotton']);
        $catNarcotics = Category::firstOrCreate(['name' => 'Controlled Narcotics'], ['description' => 'Schedule II & III dangerous controlled drugs']);
        $catAntibiotics = Category::firstOrCreate(['name' => 'Antibiotics'], ['description' => 'Oral and parenteral antimicrobials']);

        // 4. Units & Forms
        $unitCapsule = Unit::firstOrCreate(['unit_name' => 'Capsule']);
        $unitVial = Unit::firstOrCreate(['unit_name' => 'Vial']);
        $unitAmpoule = Unit::firstOrCreate(['unit_name' => 'Ampoule']);
        $unitTablet = Unit::firstOrCreate(['unit_name' => 'Tablet']);
        $unitPiece = Unit::firstOrCreate(['unit_name' => 'Piece']);

        $formInjection = MedicineForm::firstOrCreate(['form_name' => 'Injection']);
        $formCapsule = MedicineForm::firstOrCreate(['form_name' => 'Capsule']);
        $formTablet = MedicineForm::firstOrCreate(['form_name' => 'Tablet']);

        // 5. Suppliers
        $supplierSpc = Supplier::firstOrCreate(
            ['supplier_name' => 'State Pharmaceuticals Corporation'],
            ['contact_info' => 'Colombo, Sri Lanka - Tel: 011-2334455']
        );
        $supplierMed = Supplier::firstOrCreate(
            ['supplier_name' => 'Lanka Medical Supplies Ltd'],
            ['contact_info' => 'Kandy Road - Tel: 011-4556677']
        );

        // 6. Users
        $nurse = User::firstOrCreate(
            ['email' => 'nurse.ward48@hospital.gov.lk'],
            [
                'name' => 'Staff Nurse H. Perera',
                'password' => Hash::make('password123'),
                'role_id' => $nurseRole->id,
                'ward_id' => $ward48->id,
                'created_at' => now(),
            ]
        );

        $sister = User::firstOrCreate(
            ['email' => 'sister.ward48@hospital.gov.lk'],
            [
                'name' => 'Sister M. Fernando (In-Charge)',
                'password' => Hash::make('password123'),
                'role_id' => $sisterRole->id,
                'ward_id' => $ward48->id,
                'created_at' => now(),
            ]
        );

        $doctor = User::firstOrCreate(
            ['email' => 'doctor.ward48@hospital.gov.lk'],
            [
                'name' => 'Dr. K. Jayasuriya (MO)',
                'password' => Hash::make('password123'),
                'role_id' => $doctorRole->id,
                'ward_id' => $ward48->id,
                'created_at' => now(),
            ]
        );

        // 7. General Items
        $bedsheets = GeneralItem::firstOrCreate(
            ['item_code' => 'GEN-LIN-001'],
            ['name' => 'Hospital Bedsheets (White Cotton)', 'category_id' => $catLinen->id]
        );

        $blankets = GeneralItem::firstOrCreate(
            ['item_code' => 'GEN-LIN-002'],
            ['name' => 'Ward Blankets (Thermal Blue)', 'category_id' => $catLinen->id]
        );

        $pillowCases = GeneralItem::firstOrCreate(
            ['item_code' => 'GEN-LIN-003'],
            ['name' => 'Pillow Cases (Waterproof)', 'category_id' => $catLinen->id]
        );

        $gloves = GeneralItem::firstOrCreate(
            ['item_code' => 'GEN-CON-001'],
            ['name' => 'Surgical Gloves (Size 7.5 Latex)', 'category_id' => $catConsumables->id]
        );

        $syringes = GeneralItem::firstOrCreate(
            ['item_code' => 'GEN-CON-002'],
            ['name' => 'Sterile Syringes 5ml with Needle', 'category_id' => $catConsumables->id]
        );

        // 8. General Transactions
        if ($bedsheets->transactions()->count() === 0) {
            GeneralTransaction::create([
                'item_id' => $bedsheets->id,
                'ward_id' => $ward48->id,
                'quantity_received' => 60,
                'quantity_issued' => 0,
                'date' => now()->subDays(5),
                'recorded_by' => $sister->id,
            ]);

            GeneralTransaction::create([
                'item_id' => $bedsheets->id,
                'ward_id' => $ward48->id,
                'quantity_received' => 0,
                'quantity_issued' => 30,
                'date' => now()->subDays(2),
                'recorded_by' => $nurse->id,
            ]);
        }

        if ($blankets->transactions()->count() === 0) {
            GeneralTransaction::create([
                'item_id' => $blankets->id,
                'ward_id' => $ward48->id,
                'quantity_received' => 25,
                'quantity_issued' => 0,
                'date' => now()->subDays(4),
                'recorded_by' => $sister->id,
            ]);

            GeneralTransaction::create([
                'item_id' => $blankets->id,
                'ward_id' => $ward48->id,
                'quantity_received' => 0,
                'quantity_issued' => 10,
                'date' => now()->subDay(),
                'recorded_by' => $nurse->id,
            ]);
        }

        if ($pillowCases->transactions()->count() === 0) {
            // Low stock example (6 left)
            GeneralTransaction::create([
                'item_id' => $pillowCases->id,
                'ward_id' => $ward48->id,
                'quantity_received' => 20,
                'quantity_issued' => 14,
                'date' => now()->subDays(3),
                'recorded_by' => $nurse->id,
            ]);
        }

        if ($gloves->transactions()->count() === 0) {
            GeneralTransaction::create([
                'item_id' => $gloves->id,
                'ward_id' => $ward48->id,
                'quantity_received' => 150,
                'quantity_issued' => 45,
                'date' => now()->subDays(6),
                'recorded_by' => $sister->id,
            ]);
        }

        // 9. Medicines (Controlled and Non-Controlled)
        $morphine = Medicine::firstOrCreate(
            ['item_code' => 'MED-NAR-001'],
            [
                'name' => 'Morphine Sulfate Injection 10mg/ml',
                'category_id' => $catNarcotics->id,
                'unit_id' => $unitAmpoule->id,
                'form_id' => $formInjection->id,
                'strength' => '10mg/1ml',
                'is_controlled' => 1,
                'min_level' => 15,
                'warning_limit' => 25,
                'created_at' => now(),
            ]
        );

        $fentanyl = Medicine::firstOrCreate(
            ['item_code' => 'MED-NAR-002'],
            [
                'name' => 'Fentanyl Citrate Injection 50mcg/ml',
                'category_id' => $catNarcotics->id,
                'unit_id' => $unitAmpoule->id,
                'form_id' => $formInjection->id,
                'strength' => '100mcg/2ml',
                'is_controlled' => 1,
                'min_level' => 10,
                'warning_limit' => 20,
                'created_at' => now(),
            ]
        );

        $pethidine = Medicine::firstOrCreate(
            ['item_code' => 'MED-NAR-003'],
            [
                'name' => 'Pethidine HCl Injection 50mg/ml',
                'category_id' => $catNarcotics->id,
                'unit_id' => $unitAmpoule->id,
                'form_id' => $formInjection->id,
                'strength' => '50mg/1ml',
                'is_controlled' => 1,
                'min_level' => 10,
                'warning_limit' => 20,
                'created_at' => now(),
            ]
        );

        $amoxicillin = Medicine::firstOrCreate(
            ['item_code' => 'MED-ANT-001'],
            [
                'name' => 'Amoxicillin 500mg',
                'category_id' => $catAntibiotics->id,
                'unit_id' => $unitCapsule->id,
                'form_id' => $formCapsule->id,
                'strength' => '500mg',
                'is_controlled' => 0, // Not controlled
                'min_level' => 50,
                'warning_limit' => 100,
                'created_at' => now(),
            ]
        );

        // 10. Medicine Batches
        $batchMorphine = MedicineBatch::firstOrCreate(
            ['medicine_id' => $morphine->id, 'batch_no' => 'MPH-2026-B1'],
            [
                'supplier_id' => $supplierSpc->id,
                'expiry_date' => now()->addMonths(18)->toDateString(),
                'created_at' => now(),
            ]
        );

        $batchFentanyl = MedicineBatch::firstOrCreate(
            ['medicine_id' => $fentanyl->id, 'batch_no' => 'FNT-2026-A2'],
            [
                'supplier_id' => $supplierSpc->id,
                'expiry_date' => now()->addMonths(12)->toDateString(),
                'created_at' => now(),
            ]
        );

        $batchPethidine = MedicineBatch::firstOrCreate(
            ['medicine_id' => $pethidine->id, 'batch_no' => 'PTH-2026-C3'],
            [
                'supplier_id' => $supplierMed->id,
                'expiry_date' => now()->addMonths(15)->toDateString(),
                'created_at' => now(),
            ]
        );

        // Receipts for narcotics in stock_receipts (which triggers stock_ledger insertion via DB trigger)
        if (!DB::table('stock_receipts')->where('batch_id', $batchMorphine->id)->exists()) {
            DB::table('stock_receipts')->insert([
                'batch_id' => $batchMorphine->id,
                'ward_id' => $ward48->id,
                'quantity_received' => 50,
                'received_by' => $sister->id,
                'date' => now()->subDays(7),
            ]);
        }

        if (!DB::table('stock_receipts')->where('batch_id', $batchFentanyl->id)->exists()) {
            DB::table('stock_receipts')->insert([
                'batch_id' => $batchFentanyl->id,
                'ward_id' => $ward48->id,
                'quantity_received' => 30,
                'received_by' => $sister->id,
                'date' => now()->subDays(7),
            ]);
        }

        // 11. Patients and Admissions (Only patients receiving narcotics)
        $patient1 = Patient::firstOrCreate(
            ['patient_name' => 'W. M. Kamal Perera'],
            ['created_at' => now()->subDays(4)]
        );

        $admission1 = Admission::firstOrCreate(
            ['bht_no' => 'BHT-48-2026-0812'],
            [
                'patient_id' => $patient1->id,
                'ward_id' => $ward48->id,
                'admit_date' => now()->subDays(4),
                'status' => 'Admitted',
            ]
        );

        $patient2 = Patient::firstOrCreate(
            ['patient_name' => 'K. G. Sunethra Kumari'],
            ['created_at' => now()->subDays(2)]
        );

        $admission2 = Admission::firstOrCreate(
            ['bht_no' => 'BHT-48-2026-0835'],
            [
                'patient_id' => $patient2->id,
                'ward_id' => $ward48->id,
                'admit_date' => now()->subDays(2),
                'status' => 'Admitted',
            ]
        );

        // 12. Dispensations (Controlled Narcotics for Admitted Patients)
        if ($admission1->dispensations()->count() === 0) {
            Dispensation::create([
                'admission_id' => $admission1->id,
                'batch_id' => $batchMorphine->id,
                'date' => now()->subDays(2)->setTime(8, 30),
                'qty_given' => 1,
                'dosage' => '5mg IV Slow Push stat',
                'usage_time' => '08:30 AM',
                'issued_by' => $nurse->id,
                'witnessed_by' => $sister->id,
            ]);

            Dispensation::create([
                'admission_id' => $admission1->id,
                'batch_id' => $batchMorphine->id,
                'date' => now()->subDay()->setTime(14, 15),
                'qty_given' => 1,
                'dosage' => '5mg IV Slow Push prn severe pain',
                'usage_time' => '02:15 PM',
                'issued_by' => $nurse->id,
                'witnessed_by' => $doctor->id,
            ]);
        }

        if ($admission2->dispensations()->count() === 0) {
            Dispensation::create([
                'admission_id' => $admission2->id,
                'batch_id' => $batchFentanyl->id,
                'date' => now()->subHours(6),
                'qty_given' => 1,
                'dosage' => '25mcg IV post-op analgesic',
                'usage_time' => '01:00 PM',
                'issued_by' => $nurse->id,
                'witnessed_by' => $sister->id,
            ]);
        }
    }
}
