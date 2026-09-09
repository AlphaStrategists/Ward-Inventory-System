<?php

namespace DatabaseSeeders;

// Note: standard Laravel seeder class is Database\Seeders\DatabaseSeeder or DatabaseSeeder depending on namespace
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Staff;
use App\Models\Patient;
use App\Models\Item;
use App\Models\RequestedStock;
use App\Models\NarcoticUsage;
use App\Models\ConsumableUsage;
use App\Models\GeneralInventory;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Staff Members (covering all 4 roles: MS Officer, Head Nurse, Nurse, Other)
        $msOfficer = Staff::create(['name' => 'Dr. A. R. Silva', 'role' => 'MS Officer']);
        $headNurse1 = Staff::create(['name' => 'Nurse Anita K.', 'role' => 'Head Nurse']);
        $headNurse2 = Staff::create(['name' => 'Nurse Priya S.', 'role' => 'Head Nurse']);
        $nurse1 = Staff::create(['name' => 'Nurse Kamal Perera', 'role' => 'Nurse']);
        $nurse2 = Staff::create(['name' => 'Nurse Sunethra D.', 'role' => 'Nurse']);
        $otherStaff = Staff::create(['name' => 'Pharm. J. Dilan', 'role' => 'Other']);

        // 2. Patients (Only for narcotic medicine usage)
        $patient1 = Patient::create(['nic' => '198234567890', 'name' => 'K. L. Perera', 'bht' => 'BHT-47-0101']);
        $patient2 = Patient::create(['nic' => '197598765432', 'name' => 'M. S. Fernando', 'bht' => 'BHT-47-0102']);
        $patient3 = Patient::create(['nic' => '199145612378', 'name' => 'A. B. Rajapaksha', 'bht' => 'BHT-48-0205']);

        // 3. Items (11 items total, covering all 11 subtypes & all 4 workflow patterns)
        $item1 = Item::create([
            'item_name' => 'Fentanyl Injection',
            'item_type' => 'medicine',
            'item_subtype' => 'narcotic',
            'workflow_pattern' => 'narcotic_direct',
            'quantity' => 12,
        ]);

        $item2 = Item::create([
            'item_name' => 'Paracetamol Syrup',
            'item_type' => 'medicine',
            'item_subtype' => 'syrup',
            'workflow_pattern' => 'request_simple',
            'quantity' => 50,
        ]);

        $item3 = Item::create([
            'item_name' => 'Normal Saline IV 500ml',
            'item_type' => 'medicine',
            'item_subtype' => 'iv_fluid',
            'workflow_pattern' => 'request_simple',
            'quantity' => 100,
        ]);

        $item4 = Item::create([
            'item_name' => 'Diazepam 5mg Tablets',
            'item_type' => 'medicine',
            'item_subtype' => 'oral_countable',
            'workflow_pattern' => 'request_approved',
            'quantity' => 85,
        ]);

        $item5 = Item::create([
            'item_name' => 'Amoxicillin 500mg Capsules',
            'item_type' => 'medicine',
            'item_subtype' => 'oral_antibiotic',
            'workflow_pattern' => 'request_simple',
            'quantity' => 18,
        ]);

        $item6 = Item::create([
            'item_name' => 'Surgical Spirit Bulk',
            'item_type' => 'medicine',
            'item_subtype' => 'bulk_medicine',
            'workflow_pattern' => 'request_simple',
            'quantity' => 8,
        ]);

        $item7 = Item::create([
            'item_name' => 'Sterile Surgical Gloves (Pairs)',
            'item_type' => 'surgical',
            'item_subtype' => 'surgical_consumable_1',
            'workflow_pattern' => 'request_simple',
            'quantity' => 120,
        ]);

        $item8 = Item::create([
            'item_name' => 'Gauze Bandage 4-inch',
            'item_type' => 'surgical',
            'item_subtype' => 'surgical_consumable_2',
            'workflow_pattern' => 'consumable_direct',
            'quantity' => 45,
        ]);

        $item9 = Item::create([
            'item_name' => 'Iodine Antiseptic Solution',
            'item_type' => 'surgical',
            'item_subtype' => 'local_purchase',
            'workflow_pattern' => 'consumable_direct',
            'quantity' => 25,
        ]);

        $item10 = Item::create([
            'item_name' => 'Pethidine 50mg Ampoules',
            'item_type' => 'injection',
            'item_subtype' => 'injection',
            'workflow_pattern' => 'request_approved',
            'quantity' => 30,
        ]);

        $item11 = Item::create([
            'item_name' => 'Ceftriaxone 1g Injection',
            'item_type' => 'injection',
            'item_subtype' => 'injection_antibiotic',
            'workflow_pattern' => 'request_approved',
            'quantity' => 40,
        ]);

        // 4. Requested Stock (Covering both request_simple and request_approved, and all statuses)
        // Request 1: Simple request - completed
        RequestedStock::create([
            'item_id' => $item2->item_id,
            'requested_by_staff_id' => $headNurse1->staff_id,
            'request_date' => '2026-07-01',
            'required_quantity' => 30,
            'balance_before' => 20,
            'status' => 'completed',
            'confirm_request_staff_id' => $headNurse1->staff_id,
            'received_quantity' => 30,
            'received_date' => '2026-07-02',
            'confirm_received_staff_id' => $nurse1->staff_id,
        ]);

        // Request 2: Approved pattern - pending MS approval
        RequestedStock::create([
            'item_id' => $item4->item_id,
            'requested_by_staff_id' => $headNurse1->staff_id,
            'request_date' => '2026-07-05',
            'required_quantity' => 100,
            'balance_before' => 85,
            'status' => 'pending',
            'confirm_request_staff_id' => $headNurse1->staff_id,
        ]);

        // Request 3: Approved pattern - approved by MS
        RequestedStock::create([
            'item_id' => $item10->item_id,
            'requested_by_staff_id' => $headNurse2->staff_id,
            'request_date' => '2026-07-06',
            'required_quantity' => 20,
            'balance_before' => 30,
            'status' => 'approved',
            'confirm_request_staff_id' => $headNurse2->staff_id,
            'approved_by_ms_staff_id' => $msOfficer->staff_id,
        ]);

        // Request 4: Approved pattern - issued
        RequestedStock::create([
            'item_id' => $item11->item_id,
            'requested_by_staff_id' => $headNurse2->staff_id,
            'request_date' => '2026-07-08',
            'required_quantity' => 50,
            'balance_before' => 40,
            'status' => 'issued',
            'confirm_request_staff_id' => $headNurse2->staff_id,
            'approved_by_ms_staff_id' => $msOfficer->staff_id,
            'issued_officer_staff_id' => $otherStaff->staff_id,
            'issued_date' => '2026-07-09',
        ]);

        // Request 5: Low stock request
        RequestedStock::create([
            'item_id' => $item6->item_id,
            'requested_by_staff_id' => $nurse1->staff_id,
            'request_date' => '2026-07-10',
            'required_quantity' => 15,
            'balance_before' => 8,
            'status' => 'low_stock',
            'confirm_request_staff_id' => $nurse1->staff_id,
        ]);

        // 5. Narcotic Usage Log (linked to narcotic items, patients, and staff)
        NarcoticUsage::create([
            'item_id' => $item1->item_id,
            'patient_id' => $patient1->patient_id,
            'bed_no' => 'Bed 04',
            'usage_date' => '2026-07-12',
            'usage_time' => '14:30:00',
            'dosage' => '50 mcg',
            'recorded_by_staff_id' => $nurse1->staff_id,
        ]);

        NarcoticUsage::create([
            'item_id' => $item1->item_id,
            'patient_id' => $patient2->patient_id,
            'bed_no' => 'Bed 12',
            'usage_date' => '2026-07-14',
            'usage_time' => '09:15:00',
            'dosage' => '100 mcg',
            'recorded_by_staff_id' => $nurse2->staff_id,
        ]);

        // 6. Consumable Usage Log (linked to consumable items and in-charge staff)
        ConsumableUsage::create([
            'item_id' => $item8->item_id,
            'bed_head_no' => 'BHT-47-0101',
            'usage_date' => '2026-07-15',
            'quantity' => 5,
            'balance' => 45,
            'incharge_staff_id' => $headNurse1->staff_id,
            'notes' => 'Post-op dressing for Bed 04',
        ]);

        ConsumableUsage::create([
            'item_id' => $item9->item_id,
            'bed_head_no' => 'BHT-48-0205',
            'usage_date' => '2026-07-16',
            'quantity' => 2,
            'balance' => 25,
            'incharge_staff_id' => $nurse2->staff_id,
            'notes' => 'Ward routine disinfection',
        ]);

        // 7. General Inventory (Non-medical stock without staff links)
        GeneralInventory::create([
            'item_name' => 'Ward Bed Sheets (White)',
            'item_code' => 'LINEN-001',
            'entry_date' => '2026-07-01',
            'received' => 100,
            'issued' => 30,
            'balance' => 70,
        ]);

        GeneralInventory::create([
            'item_name' => 'Patient Pillow Covers',
            'item_code' => 'LINEN-002',
            'entry_date' => '2026-07-02',
            'received' => 80,
            'issued' => 20,
            'balance' => 60,
        ]);

        GeneralInventory::create([
            'item_name' => 'Thermal Blankets',
            'item_code' => 'LINEN-003',
            'entry_date' => '2026-07-05',
            'received' => 40,
            'issued' => 15,
            'balance' => 25,
        ]);
    }
}
