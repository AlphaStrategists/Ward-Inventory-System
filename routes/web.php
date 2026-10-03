<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;
use App\Http\Controllers\MedicineController;

Route::get('/test', function () {
    return "Laravel is working perfectly!";
});

Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return redirect()->route('inventory.index', ['category' => 'antibiotics']);
});

Route::get('/test-login/{role}', function ($roleName) {
    // 1. Find the Role ID
    $role = Role::where('role_name', $roleName)->first();
    
    if (!$role) {
        return "Role '{$roleName}' not found in the database. Available roles: Admin, Nurse, Doctor, Pharmacist.";
    }

    // 2. Check if a user exists with this role, otherwise create one
    $user = User::where('role_id', $role->id)->first();
    
    if (!$user) {
        // Ensure a ward exists first
        $wardId = DB::table('wards')->value('id');
        if (!$wardId) {
            $wardId = DB::table('wards')->insertGetId([
                'ward_number' => 'W-01',
                'ward_name' => 'Main Ward',
            ]);
        }

        $email = strtolower($roleName) . '@test.com';
        $user = User::create([
            'name' => "Test {$roleName}",
            'email' => $email,
            'password' => Hash::make('password'),
            'role_id' => $role->id,
            'ward_id' => $wardId,
        ]);
    }

    // 3. Log the user in
    Auth::login($user);

    // 4. Redirect to the dashboard
    return redirect('/inventory/antibiotics')->with('success', "Logged in successfully as {$roleName}!");
});

Route::prefix('inventory/{category}')->name('inventory.')->group(function () {
    Route::get('/', [MedicineController::class, 'index'])->name('index');
    Route::post('/store', [MedicineController::class, 'storeMedicine'])->name('store');
    Route::get('/{id}/details', [MedicineController::class, 'getDetails'])->name('details');
});

Route::post('/inventory/medicines/administration/{category}/{id}', [MedicineController::class, 'storeAdministration'])->name('medicines.administration.store');
Route::post('/inventory/medicines/{category}/{id}/adjustments', [App\Http\Controllers\MedicineController::class, 'storeAdjustment'])->name('inventory.medicines.adjustment.store');

Route::post('/inventory/medicines/{category}/{id}/orders', [MedicineController::class, 'storeOrder'])->name('inventory.medicines.orders.store');
Route::patch('/inventory/medicines/{category}/orders/{id}/approve', [MedicineController::class, 'approveOrder'])->name('inventory.medicines.orders.approve');
Route::patch('/inventory/medicines/{category}/orders/{id}/issue', [MedicineController::class, 'issueOrder'])->name('inventory.medicines.orders.issue');
Route::post('/inventory/medicines/{category}/orders/{id}/receive', [MedicineController::class, 'receiveOrder'])->name('inventory.medicines.orders.receive');

Route::put('/inventory/medicines/{id}', [MedicineController::class, 'update']);
Route::delete('/inventory/medicines/{id}', [MedicineController::class, 'destroy']);
