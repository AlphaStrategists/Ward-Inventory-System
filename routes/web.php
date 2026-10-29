<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Http\Controllers\SurgicalConsumableController;
use App\Http\Controllers\SurgicalConsumable2Controller;
use App\Http\Controllers\LocalPurchaseController;


/*
|--------------------------------------------------------------------------
| HOME / PORTAL
|--------------------------------------------------------------------------
*/

Route::view('/', 'portal')->name('portal');


/*
|--------------------------------------------------------------------------
| ROLE-SPECIFIC PAGE ROUTES
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| SURGICAL CONSUMABLE 1 PAGE ROUTES
|--------------------------------------------------------------------------
*/

Route::get(
    '/nurse/surgical-consumable-1',
    [SurgicalConsumableController::class, 'index']
)
    ->defaults('role', 'nurse')
    ->middleware(['auth', 'role:nurse'])
    ->name('nurse.surgical-consumable-1');

Route::get(
    '/ms/surgical-consumable-1',
    [SurgicalConsumableController::class, 'index']
)
    ->defaults('role', 'ms')
    ->middleware(['auth', 'role:ms'])
    ->name('ms.surgical-consumable-1');

Route::get(
    '/admin/surgical-consumable-1',
    [SurgicalConsumableController::class, 'index']
)
    ->defaults('role', 'admin')
    ->middleware(['auth', 'role:admin'])
    ->name('admin.surgical-consumable-1');

Route::get(
    '/pharmacist/surgical-consumable-1',
    [SurgicalConsumableController::class, 'index']
)
    ->defaults('role', 'pharmacist')
    ->middleware(['auth', 'role:pharmacist'])
    ->name('pharmacist.surgical-consumable-1');


/*
|--------------------------------------------------------------------------
| SURGICAL CONSUMABLE 2 PAGE ROUTES
|--------------------------------------------------------------------------
*/

Route::get(
    '/nurse/surgical-consumable-2',
    [SurgicalConsumable2Controller::class, 'index']
)
    ->defaults('role', 'nurse')
    ->middleware(['auth', 'role:nurse'])
    ->name('nurse.surgical-consumable-2');

Route::get(
    '/ms/surgical-consumable-2',
    [SurgicalConsumable2Controller::class, 'index']
)
    ->defaults('role', 'ms')
    ->middleware(['auth', 'role:ms'])
    ->name('ms.surgical-consumable-2');

Route::get(
    '/admin/surgical-consumable-2',
    [SurgicalConsumable2Controller::class, 'index']
)
    ->defaults('role', 'admin')
    ->middleware(['auth', 'role:admin'])
    ->name('admin.surgical-consumable-2');

Route::get(
    '/pharmacist/surgical-consumable-2',
    [SurgicalConsumable2Controller::class, 'index']
)
    ->defaults('role', 'pharmacist')
    ->middleware(['auth', 'role:pharmacist'])
    ->name('pharmacist.surgical-consumable-2');


/*
|--------------------------------------------------------------------------
| LOCAL PURCHASES PAGE ROUTES
|--------------------------------------------------------------------------
*/

Route::get(
    '/nurse/local-purchases',
    [LocalPurchaseController::class, 'index']
)
    ->defaults('role', 'nurse')
    ->middleware(['auth', 'role:nurse'])
    ->name('nurse.local-purchases');

Route::get(
    '/ms/local-purchases',
    [LocalPurchaseController::class, 'index']
)
    ->defaults('role', 'ms')
    ->middleware(['auth', 'role:ms'])
    ->name('ms.local-purchases');

Route::get(
    '/admin/local-purchases',
    [LocalPurchaseController::class, 'index']
)
    ->defaults('role', 'admin')
    ->middleware(['auth', 'role:admin'])
    ->name('admin.local-purchases');

Route::get(
    '/pharmacist/local-purchases',
    [LocalPurchaseController::class, 'index']
)
    ->defaults('role', 'pharmacist')
    ->middleware(['auth', 'role:pharmacist'])
    ->name('pharmacist.local-purchases');


/*
|--------------------------------------------------------------------------
| ROLE ROOT REDIRECT
|--------------------------------------------------------------------------
|
| /nurse  -> /nurse/surgical-consumable-1
| /ms     -> /ms/surgical-consumable-1
| /admin  -> /admin/surgical-consumable-1
| /pharmacist -> /pharmacist/surgical-consumable-1
|
| Authentication is required.
|--------------------------------------------------------------------------
*/

Route::get('/{role}', function ($role) {

    $allowedRoles = [
        'nurse',
        'ms',
        'admin',
        'pharmacist'
    ];

    if (!in_array($role, $allowedRoles, true)) {
        abort(404);
    }

    return redirect("/{$role}/surgical-consumable-1");

})
    ->where('role', 'nurse|ms|admin|pharmacist')
    ->middleware('auth');


/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

Route::post('/logout', function (Request $request) {

    if (Auth::check()) {
        Auth::logout();
    }

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');

})->middleware('auth')->name('logout');


/*
|--------------------------------------------------------------------------
| SURGICAL CONSUMABLE 1 API
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| SC1 - CONSUMABLE ITEMS
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/consumables',
    [SurgicalConsumableController::class, 'getConsumables']
)
    ->middleware('auth')
    ->name('api.sc1.consumables.index');

Route::post(
    '/api/consumables',
    [SurgicalConsumableController::class, 'storeConsumable']
)
    ->middleware(['auth', 'role:admin'])
    ->name('api.sc1.consumables.store');

Route::put(
    '/api/consumables/{id}',
    [SurgicalConsumableController::class, 'updateConsumable']
)
    ->middleware(['auth', 'role:admin'])
    ->name('api.sc1.consumables.update');

Route::delete(
    '/api/consumables/{id}',
    [SurgicalConsumableController::class, 'destroyConsumable']
)
    ->middleware(['auth', 'role:admin'])
    ->name('api.sc1.consumables.destroy');


/*
|--------------------------------------------------------------------------
| SC1 - REQUISITIONS
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/requisitions',
    [SurgicalConsumableController::class, 'getRequisitions']
)
    ->middleware('auth')
    ->name('api.sc1.requisitions.index');

Route::post(
    '/api/requisitions',
    [SurgicalConsumableController::class, 'storeRequisition']
)
    ->middleware(['auth', 'role:nurse,admin'])
    ->name('api.sc1.requisitions.store');

Route::put(
    '/api/requisitions/{id}/approve',
    [SurgicalConsumableController::class, 'approveRequisition']
)
    ->middleware(['auth', 'role:ms,admin'])
    ->name('api.sc1.requisitions.approve');

Route::put(
    '/api/requisitions/{id}/reject',
    [SurgicalConsumableController::class, 'rejectRequisition']
)
    ->middleware(['auth', 'role:ms,admin'])
    ->name('api.sc1.requisitions.reject');

Route::put(
    '/api/requisitions/{id}/receive',
    [SurgicalConsumableController::class, 'receiveRequisition']
)
    ->middleware(['auth', 'role:nurse,admin'])
    ->name('api.sc1.requisitions.receive');

Route::put(
    '/api/requisitions/{id}',
    [SurgicalConsumableController::class, 'updateRequisition']
)
    ->middleware('auth')
    ->name('api.sc1.requisitions.update');

Route::delete(
    '/api/requisitions/{id}',
    [SurgicalConsumableController::class, 'destroyRequisition']
)
    ->middleware(['auth', 'role:nurse,admin'])
    ->name('api.sc1.requisitions.destroy');


/*
|--------------------------------------------------------------------------
| SC1 - STOCK ADJUSTMENTS
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/adjustments',
    [SurgicalConsumableController::class, 'getAdjustments']
)
    ->middleware('auth')
    ->name('api.sc1.adjustments.index');

Route::post(
    '/api/adjustments',
    [SurgicalConsumableController::class, 'storeAdjustment']
)
    ->middleware(['auth', 'role:nurse,admin'])
    ->name('api.sc1.adjustments.store');

Route::delete(
    '/api/adjustments/{id}',
    [SurgicalConsumableController::class, 'destroyAdjustment']
)
    ->middleware(['auth', 'role:admin'])
    ->name('api.sc1.adjustments.destroy');


/*
|--------------------------------------------------------------------------
| SC1 - DAILY COUNTS
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/daily-counts',
    [SurgicalConsumableController::class, 'getDailyCounts']
)
    ->middleware('auth')
    ->name('api.sc1.daily-counts.index');

Route::post(
    '/api/daily-counts',
    [SurgicalConsumableController::class, 'storeDailyCount']
)
    ->middleware(['auth', 'role:admin,nurse'])
    ->name('api.sc1.daily-counts.store');


/*
|--------------------------------------------------------------------------
| SURGICAL CONSUMABLE 2 API
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| SC2 - CONSUMABLE ITEMS
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/surgical-consumable-2/consumables',
    [SurgicalConsumable2Controller::class, 'getConsumables']
)
    ->middleware('auth')
    ->name('api.sc2.consumables.index');

Route::post(
    '/api/surgical-consumable-2/consumables',
    [SurgicalConsumable2Controller::class, 'storeConsumable']
)
    ->middleware(['auth', 'role:admin'])
    ->name('api.sc2.consumables.store');

Route::put(
    '/api/surgical-consumable-2/consumables/{id}',
    [SurgicalConsumable2Controller::class, 'updateConsumable']
)
    ->middleware(['auth', 'role:admin'])
    ->name('api.sc2.consumables.update');

Route::delete(
    '/api/surgical-consumable-2/consumables/{id}',
    [SurgicalConsumable2Controller::class, 'destroyConsumable']
)
    ->middleware(['auth', 'role:admin'])
    ->name('api.sc2.consumables.destroy');


/*
|--------------------------------------------------------------------------
| SC2 - REQUISITIONS
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/surgical-consumable-2/requisitions',
    [SurgicalConsumable2Controller::class, 'getRequisitions']
)
    ->middleware('auth')
    ->name('api.sc2.requisitions.index');

Route::post(
    '/api/surgical-consumable-2/requisitions',
    [SurgicalConsumable2Controller::class, 'storeRequisition']
)
    ->middleware(['auth', 'role:nurse,admin'])
    ->name('api.sc2.requisitions.store');

Route::put(
    '/api/surgical-consumable-2/requisitions/{id}/approve',
    [SurgicalConsumable2Controller::class, 'approveRequisition']
)
    ->middleware(['auth', 'role:ms,admin'])
    ->name('api.sc2.requisitions.approve');

Route::put(
    '/api/surgical-consumable-2/requisitions/{id}/reject',
    [SurgicalConsumable2Controller::class, 'rejectRequisition']
)
    ->middleware(['auth', 'role:ms,admin'])
    ->name('api.sc2.requisitions.reject');

Route::put(
    '/api/surgical-consumable-2/requisitions/{id}/dispense',
    [SurgicalConsumable2Controller::class, 'dispenseRequisition']
)
    ->middleware(['auth', 'role:pharmacist,admin'])
    ->name('api.sc2.requisitions.dispense');

Route::put(
    '/api/surgical-consumable-2/requisitions/{id}/receive',
    [SurgicalConsumable2Controller::class, 'receiveRequisition']
)
    ->middleware(['auth', 'role:nurse,admin'])
    ->name('api.sc2.requisitions.receive');

Route::put(
    '/api/surgical-consumable-2/requisitions/{id}',
    [SurgicalConsumable2Controller::class, 'updateRequisition']
)
    ->middleware('auth')
    ->name('api.sc2.requisitions.update');

Route::delete(
    '/api/surgical-consumable-2/requisitions/{id}',
    [SurgicalConsumable2Controller::class, 'destroyRequisition']
)
    ->middleware(['auth', 'role:admin'])
    ->name('api.sc2.requisitions.destroy');


/*
|--------------------------------------------------------------------------
| SC2 - STOCK ADJUSTMENTS
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/surgical-consumable-2/adjustments',
    [SurgicalConsumable2Controller::class, 'getAdjustments']
)
    ->middleware('auth')
    ->name('api.sc2.adjustments.index');

Route::post(
    '/api/surgical-consumable-2/adjustments',
    [SurgicalConsumable2Controller::class, 'storeAdjustment']
)
    ->middleware(['auth', 'role:nurse,admin'])
    ->name('api.sc2.adjustments.store');

Route::delete(
    '/api/surgical-consumable-2/adjustments/{id}',
    [SurgicalConsumable2Controller::class, 'destroyAdjustment']
)
    ->middleware(['auth', 'role:admin'])
    ->name('api.sc2.adjustments.destroy');


/*
|--------------------------------------------------------------------------
| SC2 - DAILY COUNTS
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/surgical-consumable-2/daily-counts',
    [SurgicalConsumable2Controller::class, 'getDailyCounts']
)
    ->middleware('auth')
    ->name('api.sc2.daily-counts.index');

Route::post(
    '/api/surgical-consumable-2/daily-counts',
    [SurgicalConsumable2Controller::class, 'storeDailyCount']
)
    ->middleware(['auth', 'role:admin,nurse'])
    ->name('api.sc2.daily-counts.store');


/*
|--------------------------------------------------------------------------
| LOCAL PURCHASES API
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| LOCAL PURCHASE - ITEMS
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/local-purchases/items',
    [LocalPurchaseController::class, 'getItems']
)
    ->middleware('auth')
    ->name('api.local.items.index');

Route::post(
    '/api/local-purchases/items',
    [LocalPurchaseController::class, 'storeItem']
)
    ->middleware(['auth', 'role:admin'])
    ->name('api.local.items.store');

Route::put(
    '/api/local-purchases/items/{id}',
    [LocalPurchaseController::class, 'updateItem']
)
    ->middleware(['auth', 'role:admin'])
    ->name('api.local.items.update');

Route::delete(
    '/api/local-purchases/items/{id}',
    [LocalPurchaseController::class, 'destroyItem']
)
    ->middleware(['auth', 'role:admin'])
    ->name('api.local.items.destroy');


/*
|--------------------------------------------------------------------------
| LOCAL PURCHASE - REQUISITIONS
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/local-purchases/requisitions',
    [LocalPurchaseController::class, 'getRequisitions']
)
    ->middleware('auth')
    ->name('api.local.requisitions.index');

Route::post(
    '/api/local-purchases/requisitions',
    [LocalPurchaseController::class, 'storeRequisition']
)
    ->middleware(['auth', 'role:nurse,admin'])
    ->name('api.local.requisitions.store');

Route::put(
    '/api/local-purchases/requisitions/{id}/approve',
    [LocalPurchaseController::class, 'approveRequisition']
)
    ->middleware(['auth', 'role:ms,admin'])
    ->name('api.local.requisitions.approve');

Route::put(
    '/api/local-purchases/requisitions/{id}/reject',
    [LocalPurchaseController::class, 'rejectRequisition']
)
    ->middleware(['auth', 'role:ms,admin'])
    ->name('api.local.requisitions.reject');

Route::put(
    '/api/local-purchases/requisitions/{id}/receive',
    [LocalPurchaseController::class, 'receiveRequisition']
)
    ->middleware(['auth', 'role:nurse,admin'])
    ->name('api.local.requisitions.receive');

Route::put(
    '/api/local-purchases/requisitions/{id}',
    [LocalPurchaseController::class, 'updateRequisition']
)
    ->middleware('auth')
    ->name('api.local.requisitions.update');

Route::delete(
    '/api/local-purchases/requisitions/{id}',
    [LocalPurchaseController::class, 'destroyRequisition']
)
    ->middleware(['auth', 'role:nurse,admin'])
    ->name('api.local.requisitions.destroy');


/*
|--------------------------------------------------------------------------
| LOCAL PURCHASE - STOCK ADJUSTMENTS
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/local-purchases/adjustments',
    [LocalPurchaseController::class, 'getAdjustments']
)
    ->middleware('auth')
    ->name('api.local.adjustments.index');

Route::post(
    '/api/local-purchases/adjustments',
    [LocalPurchaseController::class, 'storeAdjustment']
)
    ->middleware(['auth', 'role:nurse,admin'])
    ->name('api.local.adjustments.store');

Route::delete(
    '/api/local-purchases/adjustments/{id}',
    [LocalPurchaseController::class, 'destroyAdjustment']
)
    ->middleware(['auth', 'role:admin'])
    ->name('api.local.adjustments.destroy');


/*
|--------------------------------------------------------------------------
| LOCAL PURCHASE - DAILY COUNTS
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/local-purchases/daily-counts',
    [LocalPurchaseController::class, 'getDailyCounts']
)
    ->middleware('auth')
    ->name('api.local.daily-counts.index');

Route::post(
    '/api/local-purchases/daily-counts',
    [LocalPurchaseController::class, 'storeDailyCount']
)
    ->middleware(['auth', 'role:admin,nurse'])
    ->name('api.local.daily-counts.store');