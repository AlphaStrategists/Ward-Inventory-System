<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\DatabaseSeeder;
use App\Models\Item;
use App\Models\Staff;
use App\Models\Patient;
use App\Models\RequestedStock;
use App\Models\NarcoticUsage;
use App\Models\ConsumableUsage;
use App\Models\GeneralInventory;

class WardInventorySystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_database_seeder_populates_all_seven_tables()
    {
        $this->assertGreaterThan(0, Staff::count());
        $this->assertGreaterThan(0, Patient::count());
        $this->assertEquals(11, Item::count());
        $this->assertGreaterThan(0, RequestedStock::count());
        $this->assertGreaterThan(0, NarcoticUsage::count());
        $this->assertGreaterThan(0, ConsumableUsage::count());
        $this->assertGreaterThan(0, GeneralInventory::count());
    }

    public function test_dashboard_renders_successfully_with_low_stock_and_ms_approvals()
    {
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Ward Inventory Dashboard');
        $response->assertSee('Low-Stock Alerts');
        $response->assertSee('Pending MS Officer Approvals');
    }

    public function test_items_index_filters_and_loads_active_transactions()
    {
        $response = $this->get(route('items.index', ['type' => 'medicine']));
        $response->assertStatus(200);

        $item = Item::where('workflow_pattern', 'request_approved')->first();
        $this->assertNotNull($item);
        $this->assertNotEmpty($item->active_transactions);
    }

    public function test_stock_requisition_approval_issue_and_receive_lifecycle()
    {
        $msOfficer = Staff::where('role', 'MS Officer')->first();
        $headNurse = Staff::where('role', 'Head Nurse')->first();
        $otherStaff = Staff::where('role', 'Other')->first();
        $item = Item::where('workflow_pattern', 'request_approved')->first();

        $initialQuantity = $item->quantity;

        // 1. Create request
        $response = $this->post(route('requested-stock.store'), [
            'item_id' => $item->item_id,
            'requested_by_staff_id' => $headNurse->staff_id,
            'request_date' => date('Y-m-d'),
            'required_quantity' => 20,
        ]);
        $response->assertRedirect();

        $req = RequestedStock::where('item_id', $item->item_id)->orderBy('request_id', 'desc')->first();
        $this->assertEquals('pending', $req->status);

        // 2. Approve MS
        $response = $this->post(route('requested-stock.approve-ms', $req->request_id), [
            'approved_by_ms_staff_id' => $msOfficer->staff_id,
        ]);
        $response->assertRedirect();
        $req->refresh();
        $this->assertEquals('approved', $req->status);

        // 3. Issue
        $response = $this->post(route('requested-stock.issue', $req->request_id), [
            'issued_officer_staff_id' => $otherStaff->staff_id,
            'issued_date' => date('Y-m-d'),
        ]);
        $response->assertRedirect();
        $req->refresh();
        $this->assertEquals('issued', $req->status);

        // 4. Receive
        $response = $this->post(route('requested-stock.receive', $req->request_id), [
            'received_quantity' => 20,
            'received_date' => date('Y-m-d'),
            'confirm_received_staff_id' => $headNurse->staff_id,
        ]);
        $response->assertRedirect();
        $req->refresh();
        $this->assertEquals('completed', $req->status);

        // Verify stock incremented
        $item->refresh();
        $this->assertEquals($initialQuantity + 20, $item->quantity);
    }

    public function test_narcotic_usage_decrements_item_stock_and_links_patient()
    {
        $narcoticItem = Item::where('workflow_pattern', 'narcotic_direct')->first();
        $nurse = Staff::where('role', 'Nurse')->first();

        $initialStock = $narcoticItem->quantity;

        $response = $this->post(route('narcotic-usage.store'), [
            'item_id' => $narcoticItem->item_id,
            'patient_name' => 'John Doe',
            'patient_nic' => '199911223344',
            'patient_bht' => 'BHT-99-999',
            'bed_no' => 'Bed 01',
            'usage_date' => date('Y-m-d'),
            'usage_time' => '10:00',
            'dosage' => '10mg',
            'recorded_by_staff_id' => $nurse->staff_id,
        ]);

        $response->assertRedirect();

        $narcoticItem->refresh();
        $this->assertEquals($initialStock - 1, $narcoticItem->quantity);

        $patient = Patient::where('nic', '199911223344')->first();
        $this->assertNotNull($patient);
        $this->assertDatabaseHas('narcotic_usage', [
            'item_id' => $narcoticItem->item_id,
            'patient_id' => $patient->patient_id,
            'dosage' => '10mg',
        ]);
    }

    public function test_consumable_usage_updates_balance()
    {
        $consumableItem = Item::where('workflow_pattern', 'consumable_direct')->first();
        $headNurse = Staff::where('role', 'Head Nurse')->first();

        $initialStock = $consumableItem->quantity;

        $response = $this->post(route('consumable-usage.store'), [
            'item_id' => $consumableItem->item_id,
            'bed_head_no' => 'BHT-10-10',
            'usage_date' => date('Y-m-d'),
            'quantity' => 5,
            'incharge_staff_id' => $headNurse->staff_id,
            'notes' => 'Test notes',
        ]);

        $response->assertRedirect();

        $consumableItem->refresh();
        $this->assertEquals($initialStock - 5, $consumableItem->quantity);
        $this->assertDatabaseHas('consumable_usage', [
            'item_id' => $consumableItem->item_id,
            'quantity' => 5,
            'balance' => $initialStock - 5,
        ]);
    }

    public function test_general_inventory_calculates_balance()
    {
        $response = $this->post(route('general-inventory.store'), [
            'item_name' => 'Hospital Blankets',
            'item_code' => 'GEN-100',
            'entry_date' => date('Y-m-d'),
            'received' => 50,
            'issued' => 10,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('general_inventory', [
            'item_name' => 'Hospital Blankets',
            'received' => 50,
            'issued' => 10,
            'balance' => 40,
        ]);
    }
}
