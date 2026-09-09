<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\RequestedStockController;
use App\Http\Controllers\NarcoticUsageController;
use App\Http\Controllers\ConsumableUsageController;
use App\Http\Controllers\GeneralInventoryController;
use App\Http\Controllers\GInventoryController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\NarcoticController;
use App\Http\Controllers\SyrupController;
use App\Http\Controllers\IvFluidController;
use App\Http\Controllers\OralCountableController;
use App\Http\Controllers\AntibioticController;
use App\Http\Controllers\OralAntibioticController;

Route::get('/inventory', [GInventoryController::class, 'index'])->name('inventory.index');
Route::post('/inventory', [GInventoryController::class, 'store'])->name('inventory.store');
Route::put('/inventory/{inventoryItem}', [GInventoryController::class, 'update'])->name('inventory.update');
Route::delete('/inventory/{inventoryItem}', [GInventoryController::class, 'destroy'])->name('inventory.destroy');

Route::get('/patients', [PatientController::class, 'index']);
Route::post('/patients', [PatientController::class, 'store']);
Route::put('/patients/{patient}', [PatientController::class, 'update']);
Route::delete('/patients/{patient}', [PatientController::class, 'destroy']);

Route::get('/narcotics', [NarcoticController::class, 'index'])->name('narcotics.index');
Route::get('/syrup', [SyrupController::class, 'index'])->name('syrup.index');
Route::get('/iv-fluid', [IvFluidController::class, 'index'])->name('iv-fluid.index');
Route::get('/oral-countable', [OralCountableController::class, 'index'])->name('oral-countable.index');
Route::get('/antibiotic', [AntibioticController::class, 'index'])->name('antibiotic.index');
Route::get('/oral-antibiotic', [OralAntibioticController::class, 'index'])->name('oral-antibiotic.index');

Route::get('/', [UserController::class, 'login'])->name('login');
Route::get('/home', [UserController::class, 'home'])->name('home');
Route::get('/password/reset', [UserController::class, 'showPasswordResetForm'])->name('password.request');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Items routes
Route::get('/items', [ItemController::class, 'index'])->name('items.index');
Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
Route::post('/items', [ItemController::class, 'store'])->name('items.store');
Route::get('/items/{id}', [ItemController::class, 'show'])->name('items.show');
Route::put('/items/{id}', [ItemController::class, 'update'])->name('items.update');

// Requested Stock routes
Route::get('/requested-stock', [RequestedStockController::class, 'index'])->name('requested-stock.index');
Route::post('/requested-stock', [RequestedStockController::class, 'store'])->name('requested-stock.store');
Route::post('/requested-stock/{id}/approve-ms', [RequestedStockController::class, 'approveMs'])->name('requested-stock.approve-ms');
Route::post('/requested-stock/{id}/issue', [RequestedStockController::class, 'issue'])->name('requested-stock.issue');
Route::post('/requested-stock/{id}/receive', [RequestedStockController::class, 'receive'])->name('requested-stock.receive');

// Narcotic Usage routes
Route::get('/narcotic-usage', [NarcoticUsageController::class, 'index'])->name('narcotic-usage.index');
Route::post('/narcotic-usage', [NarcoticUsageController::class, 'store'])->name('narcotic-usage.store');

// Consumable Usage routes
Route::get('/consumable-usage', [ConsumableUsageController::class, 'index'])->name('consumable-usage.index');
Route::post('/consumable-usage', [ConsumableUsageController::class, 'store'])->name('consumable-usage.store');

// General Inventory routes
Route::get('/general-inventory', [GeneralInventoryController::class, 'index'])->name('general-inventory.index');
Route::post('/general-inventory', [GeneralInventoryController::class, 'store'])->name('general-inventory.store');
Route::put('/general-inventory/{id}', [GeneralInventoryController::class, 'update'])->name('general-inventory.update');

// Legacy route alias for template compatibility
Route::get('/injA', function () {
    return redirect()->route('items.index');
});