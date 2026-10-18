<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Medicine;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use App\Models\Ward;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WardInventoryPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_all_pages_render(): void
    {
        foreach (['home', 'login', 'injections.index', 'injection-antibiotics.index', 'ms.index'] as $name) {
            $this->get(route($name))->assertOk();
        }
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->assertSame(5, Medicine::count());
        $this->assertSame(3, Order::count());
    }

    public function test_available_stock_comes_from_ledger_and_is_zero_without_movements(): void
    {
        $adrenalin = Medicine::where('item_code', 'MED-INJ-001')->first();
        $this->assertSame(50, $adrenalin->available_stock);

        $category = Category::where('name', 'Injection')->first();
        $empty = Medicine::create([
            'item_code' => 'MED-INJ-900', 'name' => 'Empty', 'category_id' => $category->id,
            'unit_id' => $adrenalin->unit_id, 'form_id' => $adrenalin->form_id,
            'min_level' => 30, 'warning_limit' => 50,
        ]);
        $this->assertSame(0, $empty->available_stock);
    }

    public function test_injection_page_only_lists_injection_category(): void
    {
        $this->get(route('injections.index'))
            ->assertSee('MED-INJ-001')
            ->assertDontSee('MED-ANT-001');

        $this->get(route('injection-antibiotics.index'))
            ->assertSee('MED-ANT-001')
            ->assertDontSee('MED-INJ-001');
    }

    public function test_search_does_not_escape_category_filter(): void
    {
        // "e" matches Ceftriaxone / Meropenem too, which belong to another category
        $this->get(route('injections.index', ['search_medicine' => 'e']))
            ->assertDontSee('MED-ANT-001')
            ->assertDontSee('MED-ANT-002');
    }

    public function test_record_search_stays_within_selected_medicine(): void
    {
        $dom = Medicine::where('item_code', 'MED-INJ-003')->first();
        $this->get(route('injections.index', ['medicine_id' => $dom->id, 'search_records' => 'REQ-']))
            ->assertSee('Pharmacy Orders (1)');
    }

    public function test_store_medicine_validates_and_creates(): void
    {
        $adrenalin = Medicine::first();
        $payload = [
            'item_code' => 'MED-INJ-100', 'name' => 'Atropine',
            'category_id' => $adrenalin->category_id, 'unit_id' => $adrenalin->unit_id,
            'form_id' => $adrenalin->form_id, 'strength' => '1 mg',
            'min_level' => 20, 'warning_limit' => 10,
        ];

        $this->post(route('medicines.store'), $payload)->assertSessionHasErrors('warning_limit');

        $payload['warning_limit'] = 40;
        $this->post(route('medicines.store'), $payload)->assertSessionHas('success');
        $this->assertDatabaseHas('medicines', ['item_code' => 'MED-INJ-100']);

        // duplicate code rejected
        $this->post(route('medicines.store'), $payload)->assertSessionHasErrors('item_code');
    }

    public function test_cannot_delete_medicine_with_orders_but_can_delete_unused_one(): void
    {
        $used = Medicine::where('item_code', 'MED-INJ-001')->first();
        $this->delete(route('medicines.destroy', $used))->assertSessionHasErrors('medicine');
        $this->assertDatabaseHas('medicines', ['id' => $used->id]);

        $unused = Medicine::where('item_code', 'MED-INJ-002')->first();
        $this->delete(route('medicines.destroy', $unused))->assertSessionHas('success');
        $this->assertDatabaseMissing('medicines', ['id' => $unused->id]);
    }

    public function test_order_creation_generates_unique_sequential_req_numbers(): void
    {
        $med = Medicine::first();
        $data = [
            'medicine_id' => $med->id, 'qty_requested' => 10,
            'ward_id' => Ward::first()->id, 'requested_by' => User::first()->id,
        ];

        $this->post(route('orders.store'), $data)->assertSessionHas('success');
        $this->post(route('orders.store'), $data)->assertSessionHas('success');

        $prefix = 'REQ-' . date('Ymd') . '-';
        $this->assertDatabaseHas('orders', ['req_no' => $prefix . '001']);
        $this->assertDatabaseHas('orders', ['req_no' => $prefix . '002']);
        $this->assertSame(2, OrderDetail::where('qty_requested', 10)->count());
    }

    public function test_ms_status_update_sets_and_clears_approver(): void
    {
        $order = Order::first();

        $this->patch(route('orders.updateStatus', $order), ['ms_approval_status' => 'Approved'])
            ->assertSessionHas('success');
        $order->refresh();
        $this->assertSame('Approved', $order->ms_approval_status);
        $this->assertSame('MS Officer', $order->approver->role->role_name);

        $this->patch(route('orders.updateStatus', $order), ['ms_approval_status' => 'Pending']);
        $this->assertNull($order->fresh()->approved_by);

        $this->patch(route('orders.updateStatus', $order), ['ms_approval_status' => 'Bogus'])
            ->assertSessionHasErrors('ms_approval_status');
    }

    public function test_ms_page_filters_by_status(): void
    {
        Order::first()->update(['ms_approval_status' => 'Approved']);
        $this->get(route('ms.index', ['status' => 'Approved']))
            ->assertSee('Orders List (1)');
    }
}
