<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\GeneralItem;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Patient;
use App\Models\StockLedger;
use App\Models\User;
use App\Models\Ward;
use Tests\TestCase;

class WardInventoryTest extends TestCase
{
    public function test_root_redirects_to_general_inventory(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/general-inventory');
    }

    public function test_general_inventory_page_loads_successfully(): void
    {
        $response = $this->get('/general-inventory');
        $response->assertStatus(200);
        $response->assertSee('Ward Inventory');
        $response->assertSee('General Transactions Log');
    }

    public function test_can_record_general_inventory_transaction(): void
    {
        $item = GeneralItem::first();
        $ward = Ward::first();
        $user = User::first();

        $initialBalance = $item->current_balance;

        $response = $this->post(route('general-inventory.transactions.store', $item->id), [
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
        $item = GeneralItem::first();
        $ward = Ward::first();
        $user = User::first();

        $response = $this->post(route('general-inventory.adjustments.store', $item->id), [
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
        $response = $this->get('/patients');
        $response->assertStatus(200);
        $response->assertSee('Narcotics Registry');
        $response->assertSee('Narcotic Administration Log');
    }

    public function test_can_dispense_controlled_narcotic_and_ledger_updates(): void
    {
        $patient = Patient::first();
        $admission = $patient->currentAdmission ?? $patient->admissions->first();
        $user = User::first();

        // Get a controlled medicine batch
        $batch = MedicineBatch::whereHas('medicine', function ($q) {
            $q->where('is_controlled', 1);
        })->first();

        $this->assertNotNull($batch);

        $initialLedgerCount = StockLedger::count();

        $response = $this->post(route('admissions.dispensations.store', $admission->id), [
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
        $patient = Patient::first();
        $admission = $patient->currentAdmission ?? $patient->admissions->first();
        $user = User::first();

        // Find or create non-controlled medicine batch
        $nonControlledMed = Medicine::where('is_controlled', 0)->first();
        $batch = MedicineBatch::firstOrCreate(
            ['medicine_id' => $nonControlledMed->id, 'batch_no' => 'NON-CTRL-BATCH'],
            ['expiry_date' => now()->addYear()->toDateString(), 'created_at' => now()]
        );

        $response = $this->post(route('admissions.dispensations.store', $admission->id), [
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
}
