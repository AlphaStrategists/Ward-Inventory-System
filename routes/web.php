<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\RequestedStockController;
use App\Http\Controllers\NarcoticUsageController;
use App\Http\Controllers\ConsumableUsageController;
use App\Http\Controllers\GeneralInventoryController;
use App\Http\Controllers\SurgicalConsumableController;
use App\Http\Controllers\SurgicalConsumable2Controller;
use App\Http\Controllers\LocalPurchaseController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\GInventoryController;


// Dashboard
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


// Patient routes
Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');


// General Inventory alternative controller
Route::get('/g-inventory', [GInventoryController::class, 'index'])->name('g-inventory.index');


// =====================================================
// SURGICAL CONSUMABLE 1
// =====================================================

Route::get('/', [SurgicalConsumableController::class, 'index']);

Route::get('/surgical-consumable-1', [SurgicalConsumableController::class, 'index']);

Route::get('/api/consumables', [SurgicalConsumableController::class, 'getConsumables']);
Route::post('/api/consumables', [SurgicalConsumableController::class, 'storeConsumable']);
Route::delete('/api/consumables/{id}', [SurgicalConsumableController::class, 'destroyConsumable']);

Route::get('/api/requisitions', [SurgicalConsumableController::class, 'getRequisitions']);
Route::post('/api/requisitions', [SurgicalConsumableController::class, 'storeRequisition']);
Route::delete('/api/requisitions/{id}', [SurgicalConsumableController::class, 'destroyRequisition']);


// =====================================================
// SURGICAL CONSUMABLE 2
// =====================================================

Route::get('/surgical-consumable-2', [SurgicalConsumable2Controller::class, 'index']);

Route::get('/api/surgical-consumable-2/consumables', [SurgicalConsumable2Controller::class, 'getConsumables']);
Route::post('/api/surgical-consumable-2/consumables', [SurgicalConsumable2Controller::class, 'storeConsumable']);

Route::get('/api/surgical-consumable-2/requisitions', [SurgicalConsumable2Controller::class, 'getRequisitions']);
Route::post('/api/surgical-consumable-2/requisitions', [SurgicalConsumable2Controller::class, 'storeRequisition']);


// =====================================================
// LOCAL PURCHASES
// =====================================================

Route::get('/local-purchases', [LocalPurchaseController::class, 'index']);

Route::get('/api/local-purchases/items', [LocalPurchaseController::class, 'getItems']);
Route::post('/api/local-purchases/items', [LocalPurchaseController::class, 'storeItem']);

Route::get('/api/local-purchases/requisitions', [LocalPurchaseController::class, 'getRequisitions']);
Route::post('/api/local-purchases/requisitions', [LocalPurchaseController::class, 'storeRequisition']);