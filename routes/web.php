<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TestLoginController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\DispensationController;
use App\Http\Controllers\GeneralItemController;
use App\Http\Controllers\GeneralStockAdjustmentController;
use App\Http\Controllers\GeneralTransactionController;
use App\Http\Controllers\PatientController;

// ============================================================================
// Public / Guest Routes
// ============================================================================
Route::get('/test', function () {
    return "Laravel is working perfectly!";
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// DEV-ONLY — remove before final submission.
Route::get('/test-login/{role}', [TestLoginController::class, 'login'])->name('test-login');

// ============================================================================
// Authenticated routes — every page requires login.
// ============================================================================
Route::middleware(['auth'])->group(function () {

    Route::get('/', function () {
        return redirect()->route('general-inventory.index');
    });

    // ---- General Inventory ----------------------------------------------
    Route::prefix('general-inventory')->name('general-inventory.')->group(function () {
        Route::get('/', [GeneralItemController::class, 'index'])->name('index');

        // Nurse-only write actions
        Route::middleware(['role:Staff Nurse'])->group(function () {
            Route::post('/', [GeneralItemController::class, 'store'])->name('store');
            Route::put('/{general_item}', [GeneralItemController::class, 'update'])->name('update');
            Route::delete('/{general_item}', [GeneralItemController::class, 'destroy'])->name('destroy');
            Route::post('/{general_item}/transactions', [GeneralTransactionController::class, 'store'])->name('transactions.store');
            Route::post('/{general_item}/adjustments', [GeneralStockAdjustmentController::class, 'store'])->name('adjustments.store');
        });
    });

    // ---- Medicines Inventory ----------------------------------------------
    Route::prefix('inventory/{category}')->name('inventory.')->group(function () {
        Route::get('/', [MedicineController::class, 'index'])->name('index');
        Route::post('/store', [MedicineController::class, 'storeMedicine'])->name('store');
        Route::get('/{id}/details', [MedicineController::class, 'getDetails'])->name('details');
    });

    Route::post('/inventory/medicines/administration/{category}/{id}', [MedicineController::class, 'storeAdministration'])->name('medicines.administration.store');
    Route::post('/inventory/medicines/{category}/{id}/adjustments', [MedicineController::class, 'storeAdjustment'])->name('inventory.medicines.adjustment.store');
    Route::post('/inventory/medicines/{category}/{id}/orders', [MedicineController::class, 'storeOrder'])->name('inventory.medicines.orders.store');
    Route::patch('/inventory/medicines/{category}/orders/{id}/approve', [MedicineController::class, 'approveOrder'])->name('inventory.medicines.orders.approve');
    Route::patch('/inventory/medicines/{category}/orders/{id}/issue', [MedicineController::class, 'issueOrder'])->name('inventory.medicines.orders.issue');
    Route::post('/inventory/medicines/{category}/orders/{id}/receive', [MedicineController::class, 'receiveOrder'])->name('inventory.medicines.orders.receive');
    Route::put('/inventory/medicines/{id}', [MedicineController::class, 'update']);
    Route::delete('/inventory/medicines/{id}', [MedicineController::class, 'destroy']);

    // ---- Patients & Narcotic Dispensing -----------------------------------
    Route::prefix('patients')->name('patients.')->group(function () {
        Route::get('/', [PatientController::class, 'index'])->name('index');
        Route::get('/{patient}', [PatientController::class, 'show'])->name('show');

        // Nurse-only write actions
        Route::middleware(['role:Staff Nurse'])->group(function () {
            Route::post('/', [PatientController::class, 'store'])->name('store');
            Route::post('/{patient}/admissions', [AdmissionController::class, 'store'])->name('admissions.store');
        });
    });

    // Nurse-only: admission status updates & narcotic dispensations
    Route::middleware(['role:Staff Nurse'])->group(function () {
        Route::put('/admissions/{admission}', [AdmissionController::class, 'update'])->name('admissions.update');
        Route::post('/admissions/{admission}/dispensations', [DispensationController::class, 'store'])->name('admissions.dispensations.store');
    });
});