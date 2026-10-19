<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineForm;
use App\Models\Role;
use App\Models\StockAdjustment;
use App\Models\StockReceipt;
use App\Models\Unit;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BatchExpiryTest extends TestCase
{
    public function test_artisan_command_expires_batches_and_creates_stock_adjustment_audit_trail(): void
    {
        // 1. Setup Ward, Role, and User
        $ward = Ward::firstOrCreate(
            ['ward_number' => 'W-EXP-01'],
            ['ward_name' => 'Expiry Test Ward']
        );

        $role = Role::firstOrCreate(['role_name' => 'Staff Nurse']);

        $user = User::firstOrCreate(
            ['email' => 'nurse.expire@hospital.gov.lk'],
            [
                'name' => 'Audit Nurse',
                'password' => bcrypt('password123'),
                'role_id' => $role->id,
                'ward_id' => $ward->id,
            ]
        );

        // 2. Setup Category, Unit, Form
        $category = Category::firstOrCreate(['name' => 'narcotics-test']);
        $unit = Unit::firstOrCreate(['unit_name' => 'Ampoule']);
        $form = MedicineForm::firstOrCreate(['form_name' => 'Injection']);

        // 3. Create test medicine
        $medicine = Medicine::create([
            'item_code' => 'NAR-MED-' . uniqid(),
            'name' => 'Test Morphine Sulfate 10mg',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'form_id' => $form->id,
            'strength' => '10mg/ml',
            'is_controlled' => 1,
            'min_level' => 10,
            'warning_limit' => 20,
            'units_per_pack' => 1,
            'created_at' => now(),
        ]);

        // 4. Create an expired batch (expired 5 days ago) with 40 units in stock
        $expiredBatch = MedicineBatch::create([
            'medicine_id' => $medicine->id,
            'batch_no' => 'NAR-BATCH-EXP-' . uniqid(),
            'expiry_date' => now()->subDays(5)->toDateString(),
            'created_at' => now()->subDays(15),
        ]);

        StockReceipt::create([
            'batch_id' => $expiredBatch->id,
            'ward_id' => $ward->id,
            'quantity_received' => 40,
            'received_by' => $user->id,
            'date' => now()->subDays(15),
        ]);

        // 5. Create a valid batch (expires in 90 days) with 60 units in stock
        $validBatch = MedicineBatch::create([
            'medicine_id' => $medicine->id,
            'batch_no' => 'NAR-BATCH-VAL-' . uniqid(),
            'expiry_date' => now()->addDays(90)->toDateString(),
            'created_at' => now()->subDays(2),
        ]);

        StockReceipt::create([
            'batch_id' => $validBatch->id,
            'ward_id' => $ward->id,
            'quantity_received' => 60,
            'received_by' => $user->id,
            'date' => now()->subDays(2),
        ]);

        // Prior to command: expired batch has quantity 40, valid batch has quantity 60
        $this->assertEquals(40, (int) $expiredBatch->current_balance);
        $this->assertEquals(40, (int) $expiredBatch->quantity);
        $this->assertEquals(60, (int) $validBatch->current_balance);
        $this->assertEquals(60, (int) $validBatch->quantity);

        // 6. Run the Artisan command
        $exitCode = Artisan::call('narcotics:expire-batches');
        $this->assertEquals(0, $exitCode);

        // 7. Verify StockAdjustment physical record / audit trail was created
        $adjustment = StockAdjustment::where('batch_id', $expiredBatch->id)
            ->where('reason', 'Expired')
            ->first();

        $this->assertNotNull($adjustment, 'A StockAdjustment record must be created for the expired batch');
        $this->assertEquals('deduction', $adjustment->type);
        $this->assertEquals('EXPIRY', $adjustment->adjustment_type);
        $this->assertEquals(-40, (int) $adjustment->quantity);
        $this->assertEquals('Expired', $adjustment->reason);

        // 8. Verify the expired batch quantity is now 0 and marked expired
        $expiredBatch->refresh();
        $this->assertEquals(0, (int) $expiredBatch->current_balance);
        $this->assertEquals(0, (int) $expiredBatch->quantity);
        $this->assertEquals('expired', $expiredBatch->status);

        // Valid batch remains unaffected with 60 units
        $validBatch->refresh();
        $this->assertEquals(60, (int) $validBatch->current_balance);
        $this->assertEquals(60, (int) $validBatch->quantity);

        // 9. Verify Current Ward Stock Balance calculates based on actual batch quantities (60)
        $response = $this->actingAs($user)->get(route('inventory.details', [
            'category' => $category->name,
            'id' => $medicine->id,
        ]));

        $response->assertStatus(200);

        $selectedMedicine = $response->viewData('selectedMedicine');
        $this->assertEquals(60, (int) $selectedMedicine->stock, 'Current Ward Stock Balance must reflect actual ledger balance of 60');

        $medicines = $response->viewData('medicines');
        $this->assertEquals(60, (int) $medicines->firstWhere('id', $medicine->id)->stock);

        // 10. Verify availableBatches in Add Administration dropdown only includes the valid batch
        $availableBatches = $response->viewData('availableBatches');
        $this->assertTrue($availableBatches->contains('id', $validBatch->id));
        $this->assertFalse($availableBatches->contains('id', $expiredBatch->id));
    }

    public function test_controller_expire_batches_endpoint(): void
    {
        $role = Role::firstOrCreate(['role_name' => 'Staff Nurse']);
        $ward = Ward::firstOrCreate(['ward_number' => 'W-CTRL-01'], ['ward_name' => 'Controller Ward']);

        $user = User::firstOrCreate(
            ['email' => 'nurse.ctrl@hospital.gov.lk'],
            [
                'name' => 'Ctrl Nurse',
                'password' => bcrypt('password123'),
                'role_id' => $role->id,
                'ward_id' => $ward->id,
            ]
        );

        $response = $this->actingAs($user)->postJson(route('inventory.medicines.expire-batches'));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
