<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\InjectionAntibioticController;
use App\Http\Controllers\InjectionController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\MsApprovalController;



Route::get('/', [UserController::class, 'login'])->name('login');
Route::get('/home', [UserController::class, 'home'])->name('home');
Route::get('/password/reset', [UserController::class, 'showPasswordResetForm'])->name('password.request');



plate compatibility
// Redirect root to Injections page
Route::get('/', function () {
    return redirect()->route('injections.index');
});

// Injection Inventory Page
Route::get('/injections', [InjectionController::class, 'index'])->name('injections.index');

// Injection Antibiotics Inventory Page
Route::get('/injection-antibiotics', [InjectionAntibioticController::class, 'index'])->name('injection-antibiotics.index');

// Medical Superintendent (MS) Approval Portal Page
Route::get('/ms-approval', [MsApprovalController::class, 'index'])->name('ms.index');

// Medicine CRUD
Route::post('/medicines', [MedicineController::class, 'store'])->name('medicines.store');
Route::delete('/medicines/{medicine}', [MedicineController::class, 'destroy'])->name('medicines.destroy');

// Order Requisition CRUD
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
