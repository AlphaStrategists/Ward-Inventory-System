<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SurgicalConsumableController;
use App\Http\Controllers\SurgicalConsumable2Controller;
use App\Http\Controllers\LocalPurchaseController;

Route::view('/', 'portal');

Route::prefix('{role}')
    ->where(['role' => 'admin|nurse|ms|pharmacist'])
    ->group(function () {
        Route::get('/', fn ($role) => redirect("/$role/surgical-consumable-1"));
        Route::get('/surgical-consumable-1', [SurgicalConsumableController::class, 'index']);
        Route::get('/surgical-consumable-2', [SurgicalConsumable2Controller::class, 'index']);
        Route::get('/local-purchases', [LocalPurchaseController::class, 'index']);
    });


// =====================================================
// SURGICAL CONSUMABLE 1 (Standard Routes)
// =====================================================

Route::get('/api/consumables', [SurgicalConsumableController::class, 'getConsumables']);
Route::post('/api/consumables', [SurgicalConsumableController::class, 'storeConsumable']);
Route::put('/api/consumables/{id}', [SurgicalConsumableController::class, 'updateConsumable']);
Route::delete('/api/consumables/{id}', [SurgicalConsumableController::class, 'destroyConsumable']);

Route::get('/api/requisitions', [SurgicalConsumableController::class, 'getRequisitions']);
Route::post('/api/requisitions', [SurgicalConsumableController::class, 'storeRequisition']);
Route::put('/api/requisitions/{id}', [SurgicalConsumableController::class, 'updateRequisition']);
Route::delete('/api/requisitions/{id}', [SurgicalConsumableController::class, 'destroyRequisition']);

Route::get('/api/adjustments', [SurgicalConsumableController::class, 'getAdjustments']);
Route::post('/api/adjustments', [SurgicalConsumableController::class, 'storeAdjustment']);
Route::delete('/api/adjustments/{id}', [SurgicalConsumableController::class, 'destroyAdjustment']);

Route::get('/api/daily-counts', [SurgicalConsumableController::class, 'getDailyCounts']);
Route::post('/api/daily-counts', [SurgicalConsumableController::class, 'storeDailyCount']);


// =====================================================
// SURGICAL CONSUMABLE 2
// =====================================================

Route::get('/api/surgical-consumable-2/consumables', [SurgicalConsumable2Controller::class, 'getConsumables']);
Route::post('/api/surgical-consumable-2/consumables', [SurgicalConsumable2Controller::class, 'storeConsumable']);
Route::put('/api/surgical-consumable-2/consumables/{id}', [SurgicalConsumable2Controller::class, 'updateConsumable']);
Route::delete('/api/surgical-consumable-2/consumables/{id}', [SurgicalConsumable2Controller::class, 'destroyConsumable']);

Route::get('/api/surgical-consumable-2/requisitions', [SurgicalConsumable2Controller::class, 'getRequisitions']);
Route::post('/api/surgical-consumable-2/requisitions', [SurgicalConsumable2Controller::class, 'storeRequisition']);
Route::put('/api/surgical-consumable-2/requisitions/{id}', [SurgicalConsumable2Controller::class, 'updateRequisition']);
Route::delete('/api/surgical-consumable-2/requisitions/{id}', [SurgicalConsumable2Controller::class, 'destroyRequisition']);

Route::get('/api/surgical-consumable-2/adjustments', [SurgicalConsumable2Controller::class, 'getAdjustments']);
Route::post('/api/surgical-consumable-2/adjustments', [SurgicalConsumable2Controller::class, 'storeAdjustment']);
Route::delete('/api/surgical-consumable-2/adjustments/{id}', [SurgicalConsumable2Controller::class, 'destroyAdjustment']);

Route::get('/api/surgical-consumable-2/daily-counts', [SurgicalConsumable2Controller::class, 'getDailyCounts']);
Route::post('/api/surgical-consumable-2/daily-counts', [SurgicalConsumable2Controller::class, 'storeDailyCount']);


// =====================================================
// LOCAL PURCHASES
// =====================================================

Route::get('/api/local-purchases/items', [LocalPurchaseController::class, 'getItems']);
Route::post('/api/local-purchases/items', [LocalPurchaseController::class, 'storeItem']);
Route::put('/api/local-purchases/items/{id}', [LocalPurchaseController::class, 'updateItem']);
Route::delete('/api/local-purchases/items/{id}', [LocalPurchaseController::class, 'destroyItem']);

Route::get('/api/local-purchases/requisitions', [LocalPurchaseController::class, 'getRequisitions']);
Route::post('/api/local-purchases/requisitions', [LocalPurchaseController::class, 'storeRequisition']);
Route::put('/api/local-purchases/requisitions/{id}', [LocalPurchaseController::class, 'updateRequisition']);
Route::delete('/api/local-purchases/requisitions/{id}', [LocalPurchaseController::class, 'destroyRequisition']);

Route::get('/api/local-purchases/adjustments', [LocalPurchaseController::class, 'getAdjustments']);
Route::post('/api/local-purchases/adjustments', [LocalPurchaseController::class, 'storeAdjustment']);
Route::delete('/api/local-purchases/adjustments/{id}', [LocalPurchaseController::class, 'destroyAdjustment']);

Route::get('/api/local-purchases/daily-counts', [LocalPurchaseController::class, 'getDailyCounts']);
Route::post('/api/local-purchases/daily-counts', [LocalPurchaseController::class, 'storeDailyCount']);