<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Category;
use App\Models\Dispensation;
use App\Models\GeneralItem;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineForm;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Patient;
use App\Models\Role;
use App\Models\StockLedger;
use App\Models\StockReceipt;
use App\Models\Unit;
use App\Models\User;
use App\Models\Ward;
use Tests\TestCase;

class WardInventoryTest extends TestCase
{
    private function getNurseUser(): User
    {
        $role = Role::firstOrCreate(['role_name' => 'Nurse']);
        $user = User::where('role_id', $role->id)->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Test Nurse',
                'email' => 'nurse.testcase@hospital.gov.lk',
                'password' => bcrypt('password123'),
                'role_id' => $role->id,
            ]);
        }
        return $user;
    }

    private function getDoctorUser(): User
    {
        $role = Role::firstOrCreate(['role_name' => 'Doctor']);
        $user = User::where('role_id', $role->id)->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Test Doctor',
                'email' => 'doctor.testcase@hospital.gov.lk',
                'password' => bcrypt('password123'),
                'role_id' => $role->id,
            ]);
        }
        return $user;
    }

    private function getPharmacistUser(): User
    {
        $role = Role::firstOrCreate(['role_name' => 'Pharmacist']);
        $user = User::where('role_id', $role->id)->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Test Pharmacist',
                'email' => 'pharmacist.testcase@hospital.gov.lk',
                'password' => bcrypt('password123'),
                'role_id' => $role->id,
            ]);
        }
        return $user;
    }

    public function test_root_loads_home_view(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_general_inventory_page_loads_successfully(): void
    {
        $user = $this->getNurseUser();
        $response = $this->actingAs($user)->get('/general-inventory');
        $response->assertStatus(200);
        $response->assertSee('Ward Inventory');
        $response->assertSee('General Transactions Log');
    }

    public function test_can_record_general_inventory_transaction(): void
    {
        $user = $this->getNurseUser();
        $item = GeneralItem::first();
        $ward = Ward::first();

        $initialBalance = $item->current_balance;

        $response = $this->actingAs($user)->post(route('general-inventory.transactions.store', $item->id), [
            'item_id' => $item->id,
            'ward_id' => $ward->id,
            'transaction_type' => 'RECEIPT',
            'quantity' => 15,
            'date' => now()->format('Y-m-d H:i:s'),
            'recorded_by' => $user->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('general_transactions', [
            'item_id' => $item->id,
            'quantity_received' => 15,
        ]);

        $this->assertEquals($initialBalance + 15, $item->fresh()->current_balance);
    }

    public function test_can_record_stock_adjustment(): void
    {
        $user = $this->getNurseUser();
        $item = GeneralItem::first();
        $ward = Ward::first();

        $response = $this->actingAs($user)->post(route('general-inventory.adjustments.store', $item->id), [
            'item_id' => $item->id,
            'ward_id' => $ward->id,
            'adjustment_type' => 'DAMAGED',
            'quantity' => 2,
            'reason' => 'Torn during laundry processing',
            'adjusted_by' => $user->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('general_stock_adjustments', [
            'item_id' => $item->id,
            'adjustment_type' => 'DAMAGED',
            'quantity' => -2,
        ]);
    }

    public function test_patients_narcotics_page_loads_successfully(): void
    {
        $user = $this->getNurseUser();
        $response = $this->actingAs($user)->get('/patients');
        $response->assertStatus(200);
        $response->assertSee('Narcotics Registry');
        $response->assertSee('Narcotic Administration Log');
    }

    public function test_can_dispense_controlled_narcotic_and_ledger_updates(): void
    {
        $user = $this->getNurseUser();
        $patient = Patient::first();
        $admission = $patient->currentAdmission ?? $patient->admissions->first();

        // Get a controlled medicine batch
        $batch = MedicineBatch::whereHas('medicine', function ($q) {
            $q->where('is_controlled', 1);
        })->first();

        $this->assertNotNull($batch);

        $initialLedgerCount = StockLedger::count();

        $response = $this->actingAs($user)->post(route('admissions.dispensations.store', $admission->id), [
            'admission_id' => $admission->id,
            'batch_id' => $batch->id,
            'date' => now()->format('Y-m-d'),
            'qty_given' => 1,
            'dosage' => '5mg IV stat test',
            'usage_time' => '10:00 AM',
            'issued_by' => $user->id,
            'witnessed_by' => $user->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('dispensations', [
            'admission_id' => $admission->id,
            'batch_id' => $batch->id,
            'dosage' => '5mg IV stat test',
        ]);

        // Check that the MySQL trigger trg_dispensation_ledger automatically created a stock_ledger entry
        $this->assertEquals($initialLedgerCount + 1, StockLedger::count());
        $this->assertDatabaseHas('stock_ledger', [
            'batch_id' => $batch->id,
            'transaction_type' => 'DISPENSATION',
            'quantity' => -1,
            'source_table' => 'dispensations',
        ]);
    }

    public function test_cannot_dispense_non_controlled_medicine_in_narcotic_log(): void
    {
        $user = $this->getNurseUser();
        $patient = Patient::first();
        $admission = $patient->currentAdmission ?? $patient->admissions->first();

        // Find or create non-controlled medicine batch
        $nonControlledMed = Medicine::where('is_controlled', 0)->first();
        $batch = MedicineBatch::firstOrCreate(
            ['medicine_id' => $nonControlledMed->id, 'batch_no' => 'NON-CTRL-BATCH'],
            ['expiry_date' => now()->addYear()->toDateString(), 'created_at' => now()]
        );

        $response = $this->actingAs($user)->post(route('admissions.dispensations.store', $admission->id), [
            'admission_id' => $admission->id,
            'batch_id' => $batch->id,
            'date' => now()->format('Y-m-d'),
            'qty_given' => 1,
            'dosage' => '500mg oral',
            'issued_by' => $user->id,
        ]);

        // Should fail validation rule
        $response->assertSessionHasErrors('batch_id');
    }

    public function test_narcotics_dashboard_displays_newly_added_medicine_without_stock_records(): void
    {
        $user = $this->getNurseUser();

        // 1. Create a new narcotic medicine without any initial stock (no batches, no stock_ledger)
        $narcoticCategory = \App\Models\Category::where('name', 'narcotics')->first()
            ?? \App\Models\Category::firstOrCreate(['name' => 'narcotics']);

        $itemCode = 'MED-TEST-NAR-' . rand(1000, 9999);
        $newMedicine = Medicine::create([
            'item_code' => $itemCode,
            'name' => 'OXYCODONE 10MG TAB',
            'category_id' => $narcoticCategory->id,
            'unit_id' => \App\Models\Unit::first()->id,
            'form_id' => \App\Models\MedicineForm::first()->id,
            'strength' => '10mg',
            'is_controlled' => true,
            'min_level' => 10,
            'warning_limit' => 20,
            'units_per_pack' => 10,
        ]);

        try {
            // Verify it has no batches and no stock_ledger
            $this->assertEquals(0, $newMedicine->batches()->count());

            // 2. Access the narcotics dashboard
            $response = $this->actingAs($user)->get('/inventory/narcotics');

            // Should redirect to details page of first medicine
            $response->assertRedirect();
            $detailsUrl = $response->headers->get('Location');

            // Follow the redirect
            $detailsResponse = $this->actingAs($user)->get($detailsUrl);
            $detailsResponse->assertStatus(200);

            // 3. Ensure the newly added medicine appears in the HTML sidebar list
            $detailsResponse->assertSee('OXYCODONE 10MG TAB');
            // Ensure existing narcotics also appear
            $detailsResponse->assertSee('Morphine Sulfate Injection');

            // 4. Ensure directly viewing details of the new medicine works and lists all medicines
            $directDetails = $this->actingAs($user)->get("/inventory/narcotics/{$newMedicine->id}/details");
            $directDetails->assertStatus(200);
            $directDetails->assertSee('OXYCODONE 10MG TAB');
            $directDetails->assertSee('Morphine Sulfate Injection');
        } finally {
            $newMedicine->delete();
        }
    }

    public function test_can_add_new_medicine_with_initial_stock_without_server_crash(): void
    {
        $user = $this->getNurseUser();
        $category = \App\Models\Category::firstOrCreate(['name' => 'syrups']);
        $unit = \App\Models\Unit::first();
        $form = \App\Models\MedicineForm::first();

        $itemCode = 'MED-TEST-SYR-' . rand(1000, 9999);
        $response = $this->actingAs($user)->post(route('inventory.store', ['category' => 'syrups']), [
            'item_code' => $itemCode,
            'name' => 'PARACETAMOL SYRUP TEST',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'form_id' => $form->id,
            'strength' => '120mg/5ml',
            'min_level' => 10,
            'warning_limit' => 20,
            'units_per_pack' => 1,
            'initial_stock' => 50,
            'batch_no' => 'BATCH-TEST-001',
            'expiry_date' => now()->addYear()->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $medicine = Medicine::where('item_code', $itemCode)->first();
        $this->assertNotNull($medicine);
        $this->assertEquals('PARACETAMOL SYRUP TEST', $medicine->name);

        $batch = MedicineBatch::where('medicine_id', $medicine->id)->first();
        $this->assertNotNull($batch);
        $this->assertEquals('BATCH-TEST-001', $batch->batch_no);

        $this->assertDatabaseHas('stock_receipts', [
            'batch_id' => $batch->id,
            'quantity_received' => 50,
        ]);

        $this->assertDatabaseHas('stock_ledger', [
            'batch_id' => $batch->id,
            'transaction_type' => 'RECEIPT',
            'quantity' => 50,
            'source_table' => 'stock_receipts',
        ]);

        $this->assertEquals(50, (int) $batch->current_balance);
        $this->assertEquals(50, (int) $medicine->stock);
    }

    public function test_can_add_new_medicine_with_zero_initial_stock(): void
    {
        $user = $this->getNurseUser();
        $category = \App\Models\Category::firstOrCreate(['name' => 'syrups']);
        $unit = \App\Models\Unit::first();
        $form = \App\Models\MedicineForm::first();

        $itemCode = 'MED-ZERO-' . uniqid();
        try {
            $response = $this->actingAs($user)->post(route('inventory.store', ['category' => 'syrups']), [
                'item_code' => $itemCode,
                'name' => 'ZERO STOCK MEDICINE',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'form_id' => $form->id,
                'strength' => '500mg',
                'min_level' => 5,
                'warning_limit' => 10,
                'units_per_pack' => 1,
                'initial_stock' => 0,
            ]);

            $response->assertRedirect();
            $response->assertSessionHas('success');

            $medicine = Medicine::where('item_code', $itemCode)->first();
            $this->assertNotNull($medicine);
            $this->assertEquals(0, (int) $medicine->stock);
        } finally {
            if (isset($medicine) && $medicine) {
                $medicine->delete();
            }
        }
    }

    public function test_pharmacy_order_modal_renders_units_per_pack_and_pack_suffix(): void
    {
        $user = $this->getNurseUser();
        $category = \App\Models\Category::firstOrCreate(['name' => 'syrups']);
        $unit = \App\Models\Unit::first();
        $form = \App\Models\MedicineForm::firstOrCreate(['form_name' => 'syrup']);

        $medicine = Medicine::create([
            'item_code' => 'MED-PACK-' . rand(1000, 9999),
            'name' => 'COUGH SYRUP PACK TEST',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'form_id' => $form->id,
            'strength' => '100ml',
            'min_level' => 5,
            'warning_limit' => 10,
            'units_per_pack' => 6,
        ]);

        try {
            $response = $this->actingAs($user)->get("/inventory/syrups/{$medicine->id}/details");
            $response->assertStatus(200);

            // 1. Should label "Units per Pack", NOT "Units per Bottle"
            $response->assertSee('Units per Pack');
            $response->assertDontSee('Units per Bottle');

            // 2. Suffix next to Qty Requested should show "Pack(s)"
            $response->assertSee('Pack(s)');

            // 3. Should not have an "ml" suffix next to units_per_pack
            // In the previous version, line 1302 had '{{ $baseUnitLabel }}' which was 'ml'
            $content = $response->getContent();
            $this->assertMatchesRegularExpression('/name="units_per_pack"[^>]*value="6"[^>]*class="[^"]*rounded-lg/', $content);
        } finally {
            $medicine->delete();
        }
    }

    public function test_pharmacy_order_store_multiplies_packs_by_units_per_pack(): void
    {
        $user = $this->getNurseUser();
        $category = \App\Models\Category::firstOrCreate(['name' => 'syrups']);
        $unit = \App\Models\Unit::first();
        $form = \App\Models\MedicineForm::firstOrCreate(['form_name' => 'syrup']);

        $medicine = Medicine::create([
            'item_code' => 'MED-CALC-' . rand(1000, 9999),
            'name' => 'BULK SYRUP ORDER TEST',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'form_id' => $form->id,
            'strength' => '120mg/5ml',
            'min_level' => 5,
            'warning_limit' => 10,
            'units_per_pack' => 6,
        ]);

        try {
            $reqNo = 'REQ-TEST-' . rand(1000, 9999);
            // Nurse requests 2 packs with 6 units/pack => 12 total bottles
            $response = $this->actingAs($user)->post(route('inventory.medicines.orders.store', [
                'category' => 'syrups',
                'id' => $medicine->id,
            ]), [
                'date' => now()->format('Y-m-d H:i'),
                'req_no' => $reqNo,
                'qty_requested' => 2,
                'units_per_pack' => 6,
            ]);

            $response->assertRedirect();
            $response->assertSessionHas('success');

            $order = \App\Models\Order::where('req_no', $reqNo)->first();
            $this->assertNotNull($order);

            $orderDetail = $order->details->first();
            $this->assertNotNull($orderDetail);

            // Backend must correctly multiply 2 * 6 = 12 total individual items
            $this->assertEquals(12, $orderDetail->qty_requested);
            $this->assertEquals('2 Pack(s)', $orderDetail->remark);

            // Now test doctor approval of the 12 items
            $doctor = $this->getDoctorUser();
            $approveResponse = $this->actingAs($doctor)->patch(route('inventory.medicines.orders.approve', [
                'category' => 'syrups',
                'id' => $orderDetail->id,
            ]), [
                'action' => 'Approve',
                'qty_requested' => 12,
            ]);
            $approveResponse->assertRedirect();

            $orderDetail->refresh();
            $this->assertEquals(12, $orderDetail->qty_requested);
            $this->assertEquals('Approved', $orderDetail->order->ms_approval_status);

            // Now test pharmacist issuing 12 items
            $pharmacist = $this->getPharmacistUser();
            $issueResponse = $this->actingAs($pharmacist)->patch(route('inventory.medicines.orders.issue', [
                'category' => 'syrups',
                'id' => $orderDetail->id,
            ]), [
                'qty_issued' => 12,
            ]);
            $issueResponse->assertRedirect();

            $orderDetail->refresh();
            $this->assertEquals(12, $orderDetail->qty_issued);
        } finally {
            if (isset($order)) {
                $order->details()->delete();
                $order->delete();
            }
            $medicine->delete();
        }
    }

    public function test_nurse_creator_sees_cancel_button_and_can_cancel_pending_order(): void
    {
        $creatorNurse = $this->getNurseUser();
        $otherNurse = User::create([
            'name' => 'Other Nurse',
            'email' => 'other.nurse.' . rand(1000, 9999) . '@hospital.gov.lk',
            'password' => bcrypt('password123'),
            'role_id' => Role::where('role_name', 'Nurse')->first()->id,
        ]);

        $category = \App\Models\Category::firstOrCreate(['name' => 'syrups']);
        $unit = \App\Models\Unit::first();
        $form = \App\Models\MedicineForm::firstOrCreate(['form_name' => 'syrup']);

        $medicine = Medicine::create([
            'item_code' => 'MED-CANCEL-' . rand(1000, 9999),
            'name' => 'CANCEL ORDER TEST MED',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'form_id' => $form->id,
            'strength' => '100ml',
            'min_level' => 5,
            'warning_limit' => 10,
            'units_per_pack' => 1,
        ]);

        try {
            $reqNo = 'REQ-CANCEL-' . rand(1000, 9999);
            // 1. Creator Nurse creates an order
            $this->actingAs($creatorNurse)->post(route('inventory.medicines.orders.store', [
                'category' => 'syrups',
                'id' => $medicine->id,
            ]), [
                'date' => now()->format('Y-m-d H:i'),
                'req_no' => $reqNo,
                'qty_requested' => 3,
                'units_per_pack' => 1,
            ]);

            $order = \App\Models\Order::where('req_no', $reqNo)->first();
            $this->assertNotNull($order);
            $this->assertEquals('Pending', $order->ms_approval_status);
            $this->assertEquals($creatorNurse->id, $order->requested_by);

            $detail = $order->details->first();
            $this->assertNotNull($detail);

            // 2. Creator Nurse views dashboard -> Should see Cancel button with JS confirm dialog
            $creatorView = $this->actingAs($creatorNurse)->get("/inventory/syrups/{$medicine->id}/details");
            $creatorView->assertStatus(200);
            $creatorView->assertSee('Action');
            $creatorView->assertSee('Cancel');
            $creatorView->assertSee("confirm('Are you sure you want to cancel this order?')", false);

            // 3. Other Nurse views dashboard -> Should NOT see Cancel button for this order
            $otherView = $this->actingAs($otherNurse)->get("/inventory/syrups/{$medicine->id}/details");
            $otherView->assertStatus(200);
            $this->assertStringNotContainsString('action="' . route('inventory.medicines.orders.cancel', ['category' => 'syrups', 'id' => $detail->id]) . '"', $otherView->getContent());

            // 4. Other Nurse tries to cancel via route -> Should be forbidden
            $unauthorizedCancel = $this->actingAs($otherNurse)->patch(route('inventory.medicines.orders.cancel', [
                'category' => 'syrups',
                'id' => $detail->id,
            ]));
            $unauthorizedCancel->assertSessionHas('error');
            $order->refresh();
            $this->assertEquals('Pending', $order->ms_approval_status);

            // 5. Creator Nurse cancels the order -> Should succeed
            $cancelResponse = $this->actingAs($creatorNurse)->patch(route('inventory.medicines.orders.cancel', [
                'category' => 'syrups',
                'id' => $detail->id,
            ]));
            $cancelResponse->assertRedirect();
            $cancelResponse->assertSessionHas('success');

            // 6. Verify in DB: order record is NOT deleted, status is 'Cancelled'
            $order->refresh();
            $this->assertNotNull(\App\Models\Order::find($order->id));
            $this->assertEquals('Cancelled', $order->ms_approval_status);

            // 7. Dashboard shows 'Cancelled' badge, and Cancel button is no longer visible
            $afterCancelView = $this->actingAs($creatorNurse)->get("/inventory/syrups/{$medicine->id}/details");
            $afterCancelView->assertStatus(200);
            $afterCancelView->assertSee('Cancelled');
            $this->assertStringNotContainsString('action="' . route('inventory.medicines.orders.cancel', ['category' => 'syrups', 'id' => $detail->id]) . '"', $afterCancelView->getContent());

            // 8. Trying to cancel an already cancelled order should fail
            $alreadyCancelled = $this->actingAs($creatorNurse)->patch(route('inventory.medicines.orders.cancel', [
                'category' => 'syrups',
                'id' => $detail->id,
            ]));
            $alreadyCancelled->assertSessionHas('error');

        } finally {
            if (isset($order)) {
                $order->details()->delete();
                $order->delete();
            }
            $medicine->delete();
            $otherNurse->delete();
        }
    }

    public function test_requisitions_log_dynamically_calculates_packs_and_total_after_edits(): void
    {
        $nurse = $this->getNurseUser();
        $doctor = $this->getDoctorUser();
        $pharmacist = $this->getPharmacistUser();

        $category = \App\Models\Category::firstOrCreate(['name' => 'tablets']);
        $unit = \App\Models\Unit::first();
        $form = \App\Models\MedicineForm::firstOrCreate(['form_name' => 'tablet']);

        // Medicine with 10 tablets per pack
        $medicine = Medicine::create([
            'item_code' => 'MED-SYNC-' . rand(1000, 9999),
            'name' => 'SYNC PACKS TEST TABLETS',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'form_id' => $form->id,
            'strength' => '500mg',
            'min_level' => 10,
            'warning_limit' => 20,
            'units_per_pack' => 10,
        ]);

        try {
            $reqNo = 'REQ-SYNC-' . rand(1000, 9999);
            // 1. Nurse requests 2 packs (2 * 10 = 20 tablets)
            $this->actingAs($nurse)->post(route('inventory.medicines.orders.store', [
                'category' => 'tablets',
                'id' => $medicine->id,
            ]), [
                'date' => now()->format('Y-m-d H:i'),
                'req_no' => $reqNo,
                'qty_requested' => 2,
                'units_per_pack' => 10,
            ]);

            $order = \App\Models\Order::where('req_no', $reqNo)->first();
            $detail = $order->details->first();
            $this->assertEquals(20, $detail->qty_requested);

            // 2. Doctor approves and edits quantity to 30 tablets (3 packs instead of 2)
            $this->actingAs($doctor)->patch(route('inventory.medicines.orders.approve', [
                'category' => 'tablets',
                'id' => $detail->id,
            ]), [
                'action' => 'Approve',
                'qty_requested' => 30,
            ]);

            $detail->refresh();
            $this->assertEquals(30, $detail->qty_requested);

            // 3. View dashboard as Nurse: Qty Requested should dynamically show 3 Pack(s) (Total: 30 Tablet(s))
            $nurseView = $this->actingAs($nurse)->get("/inventory/tablets/{$medicine->id}/details");
            $nurseView->assertStatus(200);
            $nurseView->assertSee('3 Pack(s)');
            $nurseView->assertSee('(Total: 30 Tablet(s))');
            // Should NOT see stale "2 Pack(s) (Total: 30 Tablet(s))"
            $this->assertStringNotContainsString('2 Pack(s) <span class="text-[11px] font-normal text-slate-500">(Total: 30 Tablet(s))</span>', $nurseView->getContent());

            // 4. Pharmacist issues 10 tablets (1 pack)
            $this->actingAs($pharmacist)->patch(route('inventory.medicines.orders.issue', [
                'category' => 'tablets',
                'id' => $detail->id,
            ]), [
                'qty_issued' => 10,
            ]);

            $detail->refresh();
            $this->assertEquals(10, $detail->qty_issued);

            // 5. View dashboard: Qty Received should dynamically show 1 Pack(s) (Total: 10 Tablet(s))
            $issuedView = $this->actingAs($nurse)->get("/inventory/tablets/{$medicine->id}/details");
            $issuedView->assertStatus(200);
            $issuedView->assertSee('1 Pack(s)');
            $issuedView->assertSee('(Total: 10 Tablet(s))');

        } finally {
            if (isset($order)) {
                $order->details()->delete();
                $order->delete();
            }
            $medicine->delete();
        }
    }

    public function test_patient_dispensations_appear_in_medicine_dashboard_and_tab_count(): void
    {
        $nurse = $this->getNurseUser(); // Has ward_id = 1

        // 1. Create a medicine and batch
        $category = Category::where('name', 'narcotics')->first() ?? Category::firstOrCreate(['name' => 'narcotics']);
        $unit = Unit::first();
        $form = MedicineForm::first();

        $medicine = Medicine::create([
            'item_code' => 'TEST-DISP-' . rand(1000, 9999),
            'name' => 'TEST DISP DRUG ' . rand(1000, 9999),
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'form_id' => $form->id,
            'strength' => '10mg',
            'is_controlled' => true,
            'min_level' => 10,
            'warning_limit' => 20,
            'units_per_pack' => 10,
        ]);

        $batch = MedicineBatch::create([
            'medicine_id' => $medicine->id,
            'batch_no' => 'BATCH-TEST-DISP-' . rand(100, 999),
            'expiry_date' => now()->addYear()->toDateString(),
        ]);

        // Initial stock receipt (50 units)
        StockReceipt::create([
            'batch_id' => $batch->id,
            'ward_id' => 2, // In ward 2 (different ward from nurse who has ward_id = 1)
            'quantity_received' => 50,
            'received_by' => $nurse->id,
            'date' => now()->subDay(),
        ]);

        // 2. Create patient and admission in Ward 2
        $patient = Patient::create([
            'patient_name' => 'John Doe Test',
        ]);

        $admission = Admission::create([
            'bht_no' => 'BHT-TEST-' . rand(1000, 9999),
            'patient_id' => $patient->id,
            'ward_id' => 2,
            'admit_date' => now()->subDays(2),
            'status' => 'Admitted',
        ]);

        // 3. Dispense 5 units to the patient
        $dispensation = Dispensation::create([
            'admission_id' => $admission->id,
            'batch_id' => $batch->id,
            'date' => now(),
            'qty_given' => 5,
            'dosage' => '5mg IV stat',
            'usage_time' => '09:30 AM',
            'issued_by' => $nurse->id,
        ]);

        try {
            // 4. Access the medicine dashboard as Nurse
            $response = $this->actingAs($nurse)->get("/inventory/narcotics/{$medicine->id}/details");
            $response->assertStatus(200);

            // 5. Assert tab count displays (1)
            $response->assertSee('Patient Administrations (1)');

            // 6. Assert dispensation row details appear in the table
            $response->assertSee($admission->bht_no);
            $response->assertSee('John Doe Test');
            $response->assertSee('5 ' . $unit->unit_name);
            $response->assertSee('5mg IV stat');
            $response->assertSee('09:30 AM');
            $response->assertSee('45 ' . $unit->unit_name); // Dynamic balance: 50 - 5 = 45

        } finally {
            $dispensation->delete();
            $admission->delete();
            $patient->delete();
            StockLedger::where('batch_id', $batch->id)->delete();
            StockReceipt::where('batch_id', $batch->id)->delete();
            $medicine->batches()->delete();
            $medicine->delete();
        }
    }

    public function test_login_page_renders_with_csrf_token(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('name="_token"', false);

        // Also assert auth.login view renders with CSRF token
        $authLoginView = view('auth.login')->render();
        $this->assertStringContainsString('name="_token"', $authLoginView);

        // Attempt login with credentials and token
        $postResponse = $this->post('/login', [
            '_token' => csrf_token(),
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);
        $postResponse->assertRedirect('/home');

        // Ensure /home loads successfully (not 404)
        $homeResponse = $this->get('/home');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Hospital Inventory Management System');
    }

    public function test_orders_navigation_link_and_columns_exist(): void
    {
        $nurse = $this->getNurseUser();
        $response = $this->actingAs($nurse)->get('/orders');
        $response->assertStatus(200);
        $response->assertSee('Pharmacy Orders & Requisitions');
        $response->assertSee('Medicine Name');
        $response->assertSee('Orders');
        $response->assertDontSee('<span>Inventory</span>', false);
        $response->assertDontSee('<span>Patients</span>', false);

        // On inventory* route, Orders and Patients are hidden
        $inventoryResponse = $this->actingAs($nurse)->get('/general-inventory');
        $inventoryResponse->assertStatus(200);
        $inventoryResponse->assertSee('<span>Inventory</span>', false);
        $inventoryResponse->assertDontSee('<span>Orders</span>', false);
        $inventoryResponse->assertDontSee('<span>Patients</span>', false);

        // On patients* route, Inventory and Orders are hidden
        $patientsResponse = $this->actingAs($nurse)->get('/patients');
        $patientsResponse->assertStatus(200);
        $patientsResponse->assertSee('<span>Patients</span>', false);
        $patientsResponse->assertDontSee('<span>Inventory</span>', false);
        $patientsResponse->assertDontSee('<span>Orders</span>', false);

        // When on /home or /dashboard, all tabs are shown normally
        $homeRequest = \Illuminate\Http\Request::create('/home', 'GET');
        app()->instance('request', $homeRequest);
        $homeHtml = view('layouts.app')->render();
        $this->assertStringContainsString('<span>Inventory</span>', $homeHtml);
        $this->assertStringContainsString('<span>Orders</span>', $homeHtml);
        $this->assertStringContainsString('<span>Patients</span>', $homeHtml);

        $dashRequest = \Illuminate\Http\Request::create('/dashboard', 'GET');
        app()->instance('request', $dashRequest);
        $dashHtml = view('layouts.app')->render();
        $this->assertStringContainsString('<span>Inventory</span>', $dashHtml);
        $this->assertStringContainsString('<span>Orders</span>', $dashHtml);
        $this->assertStringContainsString('<span>Patients</span>', $dashHtml);
    }

    public function test_centralized_orders_dashboard_requires_authentication(): void
    {
        $response = $this->get('/orders');
        $response->assertRedirect('/login');
    }

    public function test_centralized_orders_dashboard_role_filtering_and_actions(): void
    {
        $nurse = $this->getNurseUser();
        $doctor = $this->getDoctorUser();
        $pharmacist = $this->getPharmacistUser();

        // 1. Create a medicine
        $category = Category::where('name', 'narcotics')->first() ?? Category::firstOrCreate(['name' => 'narcotics']);
        $unit = Unit::first();
        $form = MedicineForm::first();

        $medicine = Medicine::create([
            'item_code' => 'MED-ORD-TEST-' . rand(1000, 9999),
            'name' => 'PARACETAMOL 500MG TAB ' . rand(1000, 9999),
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'form_id' => $form->id,
            'strength' => '500mg',
            'is_controlled' => false,
            'min_level' => 10,
            'warning_limit' => 20,
            'units_per_pack' => 10,
        ]);

        $wardId = $nurse->ward_id ?? DB::table('wards')->value('id');

        // 2. Create Order requested by Nurse
        $reqNo = 'REQ-CENTRAL-' . rand(10000, 99999);
        $order = Order::create([
            'ward_id' => $wardId,
            'req_no' => $reqNo,
            'date' => now(),
            'requested_by' => $nurse->id,
            'ms_approval_status' => 'Pending',
        ]);

        $detail = OrderDetail::create([
            'order_id' => $order->id,
            'medicine_id' => $medicine->id,
            'qty_requested' => 30, // 3 packs
            'qty_issued' => 0,
            'remark' => '3 Pack(s)',
        ]);

        try {
            // 3. Doctor checks /orders: Default view shows Pending orders
            $docView = $this->actingAs($doctor)->get('/orders');
            $docView->assertStatus(200);
            $docView->assertSee($reqNo);
            $docView->assertSee($medicine->name);
            $docView->assertSee('Approve');
            $docView->assertSee('Reject');

            // 4. Doctor approves with edited quantity 20
            $approveResp = $this->actingAs($doctor)->patch(route('orders.approve', $detail->id), [
                'action' => 'Approve',
                'qty_requested' => 20,
            ]);
            $approveResp->assertRedirect();
            $order->refresh();
            $detail->refresh();
            $this->assertEquals('Approved', $order->ms_approval_status);
            $this->assertEquals(20, $detail->qty_requested);

            // 5. Pharmacist checks /orders: Default view shows Approved orders pending issue
            $pharmView = $this->actingAs($pharmacist)->get('/orders');
            $pharmView->assertStatus(200);
            $pharmView->assertSee($reqNo);
            $pharmView->assertSee('Issue Batch');

            // 6. Pharmacist issues 20 units
            $issueResp = $this->actingAs($pharmacist)->patch(route('orders.issue', $detail->id), [
                'qty_issued' => 20,
            ]);
            $issueResp->assertRedirect();
            $detail->refresh();
            $this->assertEquals(20, $detail->qty_issued);
            $this->assertStringContainsString('[ISSUED_BY:', $detail->remark);

            // Test pending_receipt filter explicitly to ensure no ambiguous column SQL error
            $pendingReceiptView = $this->actingAs($nurse)->get('/orders?status=pending_receipt');
            $pendingReceiptView->assertStatus(200);
            $pendingReceiptView->assertSee($reqNo);

            // 7. Nurse checks /orders: Default view shows their requests with Confirm Receipt button
            $nurseView = $this->actingAs($nurse)->get('/orders');
            $nurseView->assertStatus(200);
            $nurseView->assertSee($reqNo);
            $nurseView->assertSee('Confirm Receipt');

            // 8. Nurse confirms receipt
            $receiveResp = $this->actingAs($nurse)->post(route('orders.receive', $detail->id), [
                'batch_type' => 'new',
                'new_batch_no' => 'BATCH-TEST-REC-' . rand(100, 999),
                'new_expiry_date' => now()->addYear()->toDateString(),
            ]);
            $receiveResp->assertRedirect();
            $detail->refresh();
            $this->assertStringContainsString('[RECEIVED_BY:', $detail->remark);

            // Test received filter explicitly
            $receivedView = $this->actingAs($nurse)->get('/orders?status=received');
            $receivedView->assertStatus(200);
            $receivedView->assertSee($reqNo);

            // 9. Test Cancel feature on a second pending order
            $order2 = Order::create([
                'ward_id' => $wardId,
                'req_no' => 'REQ-CANCEL-' . rand(10000, 99999),
                'date' => now(),
                'requested_by' => $nurse->id,
                'ms_approval_status' => 'Pending',
            ]);
            $detail2 = OrderDetail::create([
                'order_id' => $order2->id,
                'medicine_id' => $medicine->id,
                'qty_requested' => 10,
                'qty_issued' => 0,
            ]);

            // Assert nurse sees SweetAlert2-bound cancel form and no inline browser confirm dialog
            $nursePendingView = $this->actingAs($nurse)->get('/orders');
            $nursePendingView->assertStatus(200);
            $nursePendingView->assertSee('cancel-order-form');
            $nursePendingView->assertSee('sweetalert2@11');
            $nursePendingView->assertSee('Are you sure?');
            $nursePendingView->assertDontSee("confirm('Are you sure you want to cancel this order requisition?')");

            $cancelResp = $this->actingAs($nurse)->patch(route('orders.cancel', $detail2->id));
            $cancelResp->assertRedirect();
            $order2->refresh();
            $this->assertEquals('Cancelled', $order2->ms_approval_status);

        } finally {
            if (isset($order2)) {
                $order2->details()->delete();
                $order2->delete();
            }
            if (isset($order)) {
                $order->details()->delete();
                $order->delete();
            }
            $batchIds = $medicine->batches()->pluck('id');
            \App\Models\StockLedger::whereIn('batch_id', $batchIds)->delete();
            \App\Models\StockReceipt::whereIn('batch_id', $batchIds)->delete();
            $medicine->batches()->delete();
            $medicine->delete();
        }
    }

    public function test_doctor_can_bulk_approve_all_pending_orders_and_button_visibility(): void
    {
        $nurse = $this->getNurseUser();
        $doctor = $this->getDoctorUser();
        $pharmacist = $this->getPharmacistUser();

        $category = Category::where('name', 'tablets')->first() ?? Category::firstOrCreate(['name' => 'tablets']);
        $unit = Unit::first();
        $form = MedicineForm::first();

        $med1 = Medicine::create([
            'item_code' => 'BULK-TEST-1-' . rand(1000, 9999),
            'name' => 'BULK TEST MED 1',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'form_id' => $form->id,
            'strength' => '100mg',
            'min_level' => 10,
            'warning_limit' => 20,
            'units_per_pack' => 10,
        ]);

        $wardId = $nurse->ward_id ?? DB::table('wards')->value('id');

        // Create 2 pending orders
        $orderA = Order::create([
            'ward_id' => $wardId,
            'req_no' => 'REQ-BULK-A-' . rand(1000, 9999),
            'date' => now(),
            'requested_by' => $nurse->id,
            'ms_approval_status' => 'Pending',
        ]);
        $detailA = OrderDetail::create([
            'order_id' => $orderA->id,
            'medicine_id' => $med1->id,
            'qty_requested' => 10,
            'qty_issued' => 0,
        ]);

        $orderB = Order::create([
            'ward_id' => $wardId,
            'req_no' => 'REQ-BULK-B-' . rand(1000, 9999),
            'date' => now(),
            'requested_by' => $nurse->id,
            'ms_approval_status' => 'Pending',
        ]);
        $detailB = OrderDetail::create([
            'order_id' => $orderB->id,
            'medicine_id' => $med1->id,
            'qty_requested' => 20,
            'qty_issued' => 0,
        ]);

        try {
            // 1. Nurse view: should NOT see the Approve All button
            $nurseView = $this->actingAs($nurse)->get('/orders');
            $nurseView->assertStatus(200);
            $nurseView->assertDontSee('id="approve-all-form"', false);
            $nurseView->assertDontSee('Approve All Pending');

            // 2. Pharmacist view: should NOT see the Approve All button
            $pharmView = $this->actingAs($pharmacist)->get('/orders');
            $pharmView->assertStatus(200);
            $pharmView->assertDontSee('id="approve-all-form"', false);
            $pharmView->assertDontSee('Approve All Pending');

            // 3. Doctor view: SHOULD see Approve All button and SweetAlert popup script
            $docView = $this->actingAs($doctor)->get('/orders');
            $docView->assertStatus(200);
            $docView->assertSee('id="approve-all-form"', false);
            $docView->assertSee('Approve All');
            $docView->assertSee('Are you sure you want to approve all pending orders');

            // 4. Non-doctor tries to access approveAll route: should fail
            $unauthResp = $this->actingAs($nurse)->patch(route('orders.approveAll'));
            $unauthResp->assertRedirect();
            $unauthResp->assertSessionHas('error');
            $orderA->refresh();
            $this->assertEquals('Pending', $orderA->ms_approval_status);

            // 5. Doctor submits approveAll
            $approveAllResp = $this->actingAs($doctor)->patch(route('orders.approveAll'));
            $approveAllResp->assertRedirect();
            $approveAllResp->assertSessionHas('success');

            // 6. Both pending orders should now be Approved and approved_by set to doctor
            $orderA->refresh();
            $orderB->refresh();
            $this->assertEquals('Approved', $orderA->ms_approval_status);
            $this->assertEquals($doctor->id, $orderA->approved_by);
            $this->assertEquals('Approved', $orderB->ms_approval_status);
            $this->assertEquals($doctor->id, $orderB->approved_by);

        } finally {
            if (isset($orderA)) {
                $orderA->details()->delete();
                $orderA->delete();
            }
            if (isset($orderB)) {
                $orderB->details()->delete();
                $orderB->delete();
            }
            if (isset($med1)) {
                $med1->delete();
            }
        }
    }

    public function test_success_alert_auto_dismiss_snippet_is_rendered(): void
    {
        $nurse = $this->getNurseUser();
        $response = $this->actingAs($nurse)->get('/orders');
        $response->assertStatus(200);
        $response->assertSee('.alert-success', false);
        $response->assertSee('setTimeout', false);
        $response->assertSee('3000', false);
    }
}

