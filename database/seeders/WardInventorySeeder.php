<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SurgicalConsumable;
use App\Models\LocalPurchase;
use App\Models\PharmacyRequisition;
use App\Models\BhtRequisition;

class WardInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Surgical Consumables 1 Initial Data
        $consumables1 = [
            ['name' => 'SURGICAL GLOVES 7.5', 'type' => 'consumable_1', 'balance' => 30, 'unit' => 'Box'],
            ['name' => 'COTTON WOOL 500G', 'type' => 'consumable_1', 'balance' => 15, 'unit' => 'Roll'],
            ['name' => 'GAUZE BANDAGE 10CM', 'type' => 'consumable_1', 'balance' => 45, 'unit' => 'Roll'],
            ['name' => 'CREPE BANDAGE 7.5CM', 'type' => 'consumable_1', 'balance' => 20, 'unit' => 'Pcs'],
            ['name' => 'SURGICAL MASK 3-PLY', 'type' => 'consumable_1', 'balance' => 100, 'unit' => 'Box'],
            ['name' => 'POVIDONE IODINE 500ML', 'type' => 'consumable_1', 'balance' => 12, 'unit' => 'Bottle'],
            ['name' => 'ADHESIVE PLASTER 5CM', 'type' => 'consumable_1', 'balance' => 25, 'unit' => 'Roll'],
        ];

        foreach ($consumables1 as $item) {
            SurgicalConsumable::updateOrCreate(
                ['name' => $item['name'], 'type' => 'consumable_1'],
                $item
            );
        }

        // 2. Pharmacy Requisitions Initial Data (For Consumable 1)
        $pharmacyReqs = [
            [
                'date' => '2026-09-16',
                'supply_name' => 'SURGICAL GLOVES 7.5',
                'balance' => '30 Box',
                'requested_qty' => '10 Box',
                'requested_by' => 'Sr. Perera (RN-402)',
                'request_signature' => 'M. Perera',
                'received_qty' => '10 Box',
                'received_by' => 'Pharm. Jayawardena (PH-108)',
                'receive_signature' => 'K. Jayawardena',
            ],
            [
                'date' => '2026-09-15',
                'supply_name' => 'COTTON WOOL 500G',
                'balance' => '15 Roll',
                'requested_qty' => '5 Roll',
                'requested_by' => 'Nurse Wickramasinghe (RN-311)',
                'request_signature' => 'S. Wickramasinghe',
                'received_qty' => '5 Roll',
                'received_by' => 'Pharm. Jayawardena (PH-108)',
                'receive_signature' => 'K. Jayawardena',
            ],
            [
                'date' => '2026-09-14',
                'supply_name' => 'GAUZE BANDAGE 10CM',
                'balance' => '45 Roll',
                'requested_qty' => '15 Roll',
                'requested_by' => 'Sr. Perera (RN-402)',
                'request_signature' => 'M. Perera',
                'received_qty' => '15 Roll',
                'received_by' => 'Pharm. Silva (PH-114)',
                'receive_signature' => 'R. Silva',
            ],
        ];

        foreach ($pharmacyReqs as $req) {
            PharmacyRequisition::create($req);
        }

        // 3. Surgical Consumables 2 Initial Data
        $consumables2 = [
            ['name' => 'SURGICAL DRESSING KIT', 'type' => 'consumable_2', 'balance' => 40, 'unit' => 'Kit'],
            ['name' => 'FOLEY CATHETER 16FR', 'type' => 'consumable_2', 'balance' => 25, 'unit' => 'Pcs'],
            ['name' => 'SUCTION CATHETER 14FR', 'type' => 'consumable_2', 'balance' => 50, 'unit' => 'Pcs'],
            ['name' => 'IV CANNULA 20G', 'type' => 'consumable_2', 'balance' => 120, 'unit' => 'Pcs'],
            ['name' => 'SCALP VEIN SET 23G', 'type' => 'consumable_2', 'balance' => 60, 'unit' => 'Pcs'],
            ['name' => 'TRACHEOSTOMY TUBE 7.5', 'type' => 'consumable_2', 'balance' => 8, 'unit' => 'Pcs'],
        ];

        foreach ($consumables2 as $item) {
            SurgicalConsumable::updateOrCreate(
                ['name' => $item['name'], 'type' => 'consumable_2'],
                $item
            );
        }

        // 4. Requisitions (Surgical Consumable 2) Initial Data
        $bhtReqsSurgical2 = [
            [
                'category_type' => 'surgical_consumable_2',
                'date' => '2026-09-16',
                'item_name' => 'SURGICAL DRESSING KIT',
                'quantity' => '2 Kit',
                'balance' => '40 Kit',
                'officer_name' => 'Sr. Fernando (RN-204)',
                'signature_in_charge' => 'K. Fernando',
                'note' => 'Issued for Emergency OT Dressing',
            ],
            [
                'category_type' => 'surgical_consumable_2',
                'date' => '2026-09-15',
                'item_name' => 'FOLEY CATHETER 16FR',
                'quantity' => '1 Pcs',
                'balance' => '25 Pcs',
                'officer_name' => 'Nurse Ratnayake (RN-512)',
                'signature_in_charge' => 'S. Ratnayake',
                'note' => 'Catheterization ward procedure',
            ],
            [
                'category_type' => 'surgical_consumable_2',
                'date' => '2026-09-14',
                'item_name' => 'IV CANNULA 20G',
                'quantity' => '5 Pcs',
                'balance' => '120 Pcs',
                'officer_name' => 'Sr. Fernando (RN-204)',
                'signature_in_charge' => 'K. Fernando',
                'note' => 'Routine IV line change',
            ],
        ];

        foreach ($bhtReqsSurgical2 as $req) {
            BhtRequisition::create($req);
        }

        // 5. Local Purchases Initial Data
        $localPurchases = [
            ['name' => 'PARACETAMOL 500MG (LP)', 'balance' => 500, 'unit' => 'Tabs'],
            ['name' => 'SYRUP AMBROXOL 100ML', 'balance' => 24, 'unit' => 'Bottle'],
            ['name' => 'HAND SANITIZER 500ML', 'balance' => 18, 'unit' => 'Bottle'],
            ['name' => 'DIGITAL THERMOMETER', 'balance' => 10, 'unit' => 'Pcs'],
            ['name' => 'NEBULIZER MASK (ADULT)', 'balance' => 35, 'unit' => 'Pcs'],
            ['name' => 'N95 RESPIRATOR MASK', 'balance' => 80, 'unit' => 'Pcs'],
        ];

        foreach ($localPurchases as $item) {
            LocalPurchase::updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }

        // 6. Requisitions (Local Purchases) Initial Data
        $bhtReqsLocal = [
            [
                'category_type' => 'local_purchases',
                'date' => '2026-09-16',
                'item_name' => 'PARACETAMOL 500MG (LP)',
                'quantity' => '20 Tabs',
                'balance' => '500 Tabs',
                'officer_name' => 'Sr. Perera (RN-402)',
                'signature_in_charge' => 'M. Perera',
                'note' => 'Issued for urgent inpatient fever management',
            ],
            [
                'category_type' => 'local_purchases',
                'date' => '2026-09-15',
                'item_name' => 'SYRUP AMBROXOL 100ML',
                'quantity' => '1 Bottle',
                'balance' => '24 Bottle',
                'officer_name' => 'Nurse De Silva (RN-118)',
                'signature_in_charge' => 'A. De Silva',
                'note' => 'Pediatric Ward local purchase issue',
            ],
            [
                'category_type' => 'local_purchases',
                'date' => '2026-09-14',
                'item_name' => 'NEBULIZER MASK (ADULT)',
                'quantity' => '2 Pcs',
                'balance' => '35 Pcs',
                'officer_name' => 'Sr. Perera (RN-402)',
                'signature_in_charge' => 'M. Perera',
                'note' => 'Asthma nebulization treatment',
            ],
        ];

        foreach ($bhtReqsLocal as $req) {
            BhtRequisition::create($req);
        }
    }
}
