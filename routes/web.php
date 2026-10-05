<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TestLoginController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\DispensationController;
use App\Http\Controllers\GeneralItemController;
use App\Http\Controllers\GeneralStockAdjustmentController;
use App\Http\Controllers\GeneralTransactionController;
use App\Http\Controllers\PatientController;
use Illuminate\Support\Facades\Route;

// ============================================================================
// Guest routes (login)
// ============================================================================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// DEV-ONLY — remove before final submission.
// Example: http://127.0.0.1:8000/test-login/Nurse
Route::get('/test-login/{role}', [TestLoginController::class, 'login'])->name('test-login');
// ============================================================================
// Authenticated routes — every page requires login.
// Only 'Staff Nurse' may create/update/delete records (see role:'Staff Nurse'
// on the write routes below). All roles can view.
// ============================================================================
Route::middleware(['auth'])->group(function () {

    Route::get('/', function () {
        return redirect()->route('general-inventory.index');
    });

    // ---- General Inventory ----------------------------------------------
    Route::prefix('general-inventory')->name('general-inventory.')->group(function () {
        // Viewable by every logged-in role
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

    // ---- Patients & Narcotic Dispensing -----------------------------------
    Route::prefix('patients')->name('patients.')->group(function () {
        // Viewable by every logged-in role
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