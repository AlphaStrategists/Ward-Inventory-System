<?php

namespace Tests\Feature;

use App\Models\InjectionAntibioticRequisition;
use App\Models\InjectionMedicine;
use App\Models\InjectionRequisition;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InjectionInventoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // STRICT ISOLATION: Force in-memory SQLite for all tests
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);

        DB::purge();
        DB::reconnect('sqlite');

        // Create minimal isolated users table in memory
        Schema::dropIfExists('injection_antibiotic_requisitions');
        Schema::dropIfExists('injection_requisitions');
        Schema::dropIfExists('injection_medicines');
        Schema::dropIfExists('users');

        Schema::create('users', function ($table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->string('role')->nullable();
            $table->integer('role_id')->nullable();
            $table->integer('ward_id')->nullable();
            $table->timestamps();
        });

        // Run additive migrations against the in-memory SQLite database
        (require database_path('migrations/2026_11_07_000001_create_injection_medicines_table.php'))->up();
        (require database_path('migrations/2026_11_07_000002_create_injection_requisitions_table.php'))->up();
        (require database_path('migrations/2026_11_07_000003_create_injection_antibiotic_requisitions_table.php'))->up();
    }

    protected function createUser(string $role, bool $isAdmin = false): User
    {
        return User::create([
            'email' => strtolower($role) . '_' . uniqid() . '@example.com',
            'name' => ucfirst($role) . ' User',
            'first_name' => ucfirst($role),
            'last_name' => 'User',
            'role' => $role,
            'is_admin' => $isAdmin,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('injection.index'));
        $response->assertRedirect(route('login'));

        $antibioticsResponse = $this->get(route('injection.antibiotics'));
        $antibioticsResponse->assertRedirect(route('login'));
    }

    public function test_nurse_can_access_both_pages(): void
    {
        $nurse = $this->createUser('Nurse');

        $this->actingAs($nurse)
            ->get(route('injection.index'))
            ->assertOk()
            ->assertSee('Ward Inventory')
            ->assertSee('Injection');

        $this->actingAs($nurse)
            ->get(route('injection.antibiotics'))
            ->assertOk()
            ->assertSee('Ward Inventory')
            ->assertSee('Injection Antibiotics');
    }

    public function test_admin_can_access_both_pages(): void
    {
        $admin = $this->createUser('Admin', true);

        $this->actingAs($admin)
            ->get(route('injection.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('injection.antibiotics'))
            ->assertOk();
    }

    public function test_doctor_and_ms_can_access_both_pages(): void
    {
        $doctor = $this->createUser('Doctor');

        $this->actingAs($doctor)
            ->get(route('injection.index'))
            ->assertOk();

        $this->actingAs($doctor)
            ->get(route('injection.antibiotics'))
            ->assertOk();

        $ms = $this->createUser('MS');

        $this->actingAs($ms)
            ->get(route('injection.index'))
            ->assertOk();

        $this->actingAs($ms)
            ->get(route('injection.antibiotics'))
            ->assertOk();
    }

    public function test_unauthorized_roles_are_denied_with_403(): void
    {
        $pharmacist = $this->createUser('Pharmacist');

        $this->actingAs($pharmacist)
            ->get(route('injection.index'))
            ->assertForbidden();

        $this->actingAs($pharmacist)
            ->get(route('injection.antibiotics'))
            ->assertForbidden();
    }

    public function test_home_navigation_includes_injection_submenu_items(): void
    {
        $nurse = $this->createUser('Nurse');

        $response = $this->actingAs($nurse)->get(route('home'));
        $response->assertOk();
        $response->assertSee(route('injection.index'));
        $response->assertSee(route('injection.antibiotics'));
        $response->assertSee('Injection Antibiotics');
    }

    public function test_add_medicine_works_independently_for_injection_and_antibiotics(): void
    {
        $nurse = $this->createUser('Nurse');

        // Add injection medicine
        $res1 = $this->actingAs($nurse)->post(route('injection.medicines.store'), [
            'name' => 'Furosemide 20mg',
        ]);
        $res1->assertRedirect();
        $this->assertDatabaseHas('injection_medicines', [
            'name' => 'Furosemide 20mg',
            'type' => 'injection',
        ]);

        // Add antibiotic medicine
        $res2 = $this->actingAs($nurse)->post(route('injection.antibiotics.medicines.store'), [
            'name' => 'Ceftriaxone 1g',
        ]);
        $res2->assertRedirect();
        $this->assertDatabaseHas('injection_medicines', [
            'name' => 'Ceftriaxone 1g',
            'type' => 'injection_antibiotics',
        ]);

        // Same name across different types is allowed
        $res3 = $this->actingAs($nurse)->post(route('injection.medicines.store'), [
            'name' => 'Ceftriaxone 1g',
        ]);
        $res3->assertRedirect();
        $this->assertDatabaseHas('injection_medicines', [
            'name' => 'Ceftriaxone 1g',
            'type' => 'injection',
        ]);
    }

    public function test_cannot_add_duplicate_medicine_in_same_category(): void
    {
        $nurse = $this->createUser('Nurse');

        InjectionMedicine::create([
            'type' => 'injection',
            'name' => 'Gentamicin 80mg',
            'is_active' => true,
        ]);

        $response = $this->actingAs($nurse)->post(route('injection.medicines.store'), [
            'name' => 'Gentamicin 80mg',
        ]);
        $response->assertSessionHasErrors('name');
    }

    public function test_add_injection_order_works_and_sets_status_to_pending(): void
    {
        $nurse = $this->createUser('Nurse');
        $medicine = InjectionMedicine::create([
            'type' => 'injection',
            'name' => 'Atropine 1mg',
            'is_active' => true,
        ]);

        $response = $this->actingAs($nurse)->post(route('injection.orders.store'), [
            'date' => '2026-11-07',
            'medicine_id' => $medicine->id,
            'requested_quantity' => 25,
            'request_signature' => 'Nurse Kamal',
        ]);

        $response->assertRedirect(route('injection.index', ['medicine' => $medicine->id]));
        $this->assertDatabaseHas('injection_requisitions', [
            'medicine_id' => $medicine->id,
            'requested_quantity' => 25,
            'request_signature' => 'Nurse Kamal',
            'ms_approval_status' => 'Pending',
        ]);
    }

    public function test_add_antibiotic_order_works_and_stores_balance(): void
    {
        $nurse = $this->createUser('Nurse');
        $medicine = InjectionMedicine::create([
            'type' => 'injection_antibiotics',
            'name' => 'Meropenem 1g',
            'is_active' => true,
        ]);

        $response = $this->actingAs($nurse)->post(route('injection.antibiotics.orders.store'), [
            'date' => '2026-11-07',
            'medicine_id' => $medicine->id,
            'balance' => 12,
            'required_quantity' => 30,
            'request_signature' => 'Nurse Sunimal',
        ]);

        $response->assertRedirect(route('injection.antibiotics', ['medicine' => $medicine->id]));
        $this->assertDatabaseHas('injection_antibiotic_requisitions', [
            'medicine_id' => $medicine->id,
            'balance' => 12,
            'required_quantity' => 30,
            'request_signature' => 'Nurse Sunimal',
        ]);
    }

    public function test_doctor_or_ms_and_admin_can_update_ms_approval(): void
    {
        $doctor = $this->createUser('Doctor');
        $medicine = InjectionMedicine::create([
            'type' => 'injection',
            'name' => 'Hydrocortisone 100mg',
            'is_active' => true,
        ]);

        $req = InjectionRequisition::create([
            'medicine_id' => $medicine->id,
            'date' => '2026-11-07',
            'requested_quantity' => 10,
            'request_signature' => 'Nurse Mary',
            'ms_approval_status' => 'Pending',
        ]);

        $response = $this->actingAs($doctor)->put(route('injection.orders.update', $req->id), [
            'date' => '2026-11-07',
            'medicine_id' => $medicine->id,
            'requested_quantity' => 10,
            'request_signature' => 'Nurse Mary',
            'ms_approval_status' => 'Approved',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('injection_requisitions', [
            'id' => $req->id,
            'ms_approval_status' => 'Approved',
            'ms_approved_by' => $doctor->id,
        ]);
    }

    public function test_nurse_cannot_change_ms_approval_status(): void
    {
        $nurse = $this->createUser('Nurse');
        $medicine = InjectionMedicine::create([
            'type' => 'injection',
            'name' => 'Diazepam 10mg',
            'is_active' => true,
        ]);

        $req = InjectionRequisition::create([
            'medicine_id' => $medicine->id,
            'date' => '2026-11-07',
            'requested_quantity' => 5,
            'request_signature' => 'Nurse Mary',
            'ms_approval_status' => 'Pending',
        ]);

        $this->actingAs($nurse)->put(route('injection.orders.update', $req->id), [
            'date' => '2026-11-07',
            'medicine_id' => $medicine->id,
            'requested_quantity' => 5,
            'request_signature' => 'Nurse Mary',
            'ms_approval_status' => 'Approved',
        ]);

        // Status should still remain Pending because nurse cannot approve
        $this->assertDatabaseHas('injection_requisitions', [
            'id' => $req->id,
            'ms_approval_status' => 'Pending',
            'ms_approved_by' => null,
        ]);
    }

    public function test_edit_order_updates_correct_existing_row(): void
    {
        $nurse = $this->createUser('Nurse');
        $medicine = InjectionMedicine::create([
            'type' => 'injection_antibiotics',
            'name' => 'Vancomycin 500mg',
            'is_active' => true,
        ]);

        $order = InjectionAntibioticRequisition::create([
            'medicine_id' => $medicine->id,
            'date' => '2026-11-07',
            'balance' => 4,
            'required_quantity' => 15,
            'request_signature' => 'Nurse Mary',
        ]);

        $response = $this->actingAs($nurse)->put(route('injection.antibiotics.orders.update', $order->id), [
            'date' => '2026-11-07',
            'medicine_id' => $medicine->id,
            'balance' => 4,
            'required_quantity' => 20, // Updated
            'request_signature' => 'Nurse Mary',
            'received_quantity' => 20,
            'received_signature' => 'Nurse John',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('injection_antibiotic_requisitions', [
            'id' => $order->id,
            'required_quantity' => 20,
            'received_quantity' => 20,
            'received_signature' => 'Nurse John',
        ]);
        // Confirm no duplicate row was created
        $this->assertEquals(1, InjectionAntibioticRequisition::count());
    }

    public function test_delete_order_soft_deletes_only_requisition_and_preserves_medicine(): void
    {
        $nurse = $this->createUser('Nurse');
        $medicine = InjectionMedicine::create([
            'type' => 'injection',
            'name' => 'Adrenaline 1mg',
            'is_active' => true,
        ]);

        $order = InjectionRequisition::create([
            'medicine_id' => $medicine->id,
            'date' => '2026-11-07',
            'requested_quantity' => 10,
            'request_signature' => 'Nurse Mary',
        ]);

        $this->actingAs($nurse)->delete(route('injection.orders.destroy', $order->id));

        // Order is soft deleted (null in normal query, exists in withTrashed)
        $this->assertSoftDeleted('injection_requisitions', ['id' => $order->id]);

        // Medicine master record remains completely untouched
        $this->assertDatabaseHas('injection_medicines', [
            'id' => $medicine->id,
            'name' => 'Adrenaline 1mg',
            'deleted_at' => null,
        ]);
    }
}
