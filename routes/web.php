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
|
| Roles:
|   nurse
|   ms
|   admin
|   pharmacist
|
| Each module has its own controller.
|
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
    ->middleware('role:nurse')
    ->name('nurse.surgical-consumable-1');

Route::get(
    '/ms/surgical-consumable-1',
    [SurgicalConsumableController::class, 'index']
)
    ->defaults('role', 'ms')
    ->middleware('role:ms')
    ->name('ms.surgical-consumable-1');

Route::get(
    '/admin/surgical-consumable-1',
    [SurgicalConsumableController::class, 'index']
)
    ->defaults('role', 'admin')
    ->middleware('role:admin')
    ->name('admin.surgical-consumable-1');

Route::get(
    '/pharmacist/surgical-consumable-1',
    [SurgicalConsumableController::class, 'index']
)
    ->defaults('role', 'pharmacist')
    ->middleware('role:pharmacist')
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
    ->middleware('role:nurse')
    ->name('nurse.surgical-consumable-2');

Route::get(
    '/ms/surgical-consumable-2',
    [SurgicalConsumable2Controller::class, 'index']
)
    ->defaults('role', 'ms')
    ->middleware('role:ms')
    ->name('ms.surgical-consumable-2');

Route::get(
    '/admin/surgical-consumable-2',
    [SurgicalConsumable2Controller::class, 'index']
)
    ->defaults('role', 'admin')
    ->middleware('role:admin')
    ->name('admin.surgical-consumable-2');

Route::get(
    '/pharmacist/surgical-consumable-2',
    [SurgicalConsumable2Controller::class, 'index']
)
    ->defaults('role', 'pharmacist')
    ->middleware('role:pharmacist')
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
    ->middleware('role:nurse')
    ->name('nurse.local-purchases');

Route::get(
    '/ms/local-purchases',
    [LocalPurchaseController::class, 'index']
)
    ->defaults('role', 'ms')
    ->middleware('role:ms')
    ->name('ms.local-purchases');

Route::get(
    '/admin/local-purchases',
    [LocalPurchaseController::class, 'index']
)
    ->defaults('role', 'admin')
    ->middleware('role:admin')
    ->name('admin.local-purchases');

Route::get(
    '/pharmacist/local-purchases',
    [LocalPurchaseController::class, 'index']
)
    ->defaults('role', 'pharmacist')
    ->middleware('role:pharmacist')
    ->name('pharmacist.local-purchases');


/*
|--------------------------------------------------------------------------
| ROLE ROOT REDIRECT
|--------------------------------------------------------------------------
|
| Example:
| /nurse
| /ms
| /admin
| /pharmacist
|
| Redirects to Surgical Consumable 1.
|
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
    ->where('role', 'nurse|ms|admin|pharmacist');


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

})->name('logout');


/*
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
| SURGICAL CONSUMABLE 1 API
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
|
| Controller:
| App\Http\Controllers\SurgicalConsumableController
|
*/


/*
|--------------------------------------------------------------------------
| SC1 - CONSUMABLE ITEMS
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/consumables',
    [SurgicalConsumableController::class, 'getConsumables']
)->name('api.sc1.consumables.index');

Route::post(
    '/api/consumables',
    [SurgicalConsumableController::class, 'storeConsumable']
)->middleware('role:admin')
 ->name('api.sc1.consumables.store');

Route::put(
    '/api/consumables/{id}',
    [SurgicalConsumableController::class, 'updateConsumable']
)->middleware('role:admin')
 ->name('api.sc1.consumables.update');

Route::delete(
    '/api/consumables/{id}',
    [SurgicalConsumableController::class, 'destroyConsumable']
)->middleware('role:admin')
 ->name('api.sc1.consumables.destroy');


/*
|--------------------------------------------------------------------------
| SC1 - REQUISITIONS
|--------------------------------------------------------------------------
*/

/*
 * Get requisitions
 */
Route::get(
    '/api/requisitions',
    [SurgicalConsumableController::class, 'getRequisitions']
)->name('api.sc1.requisitions.index');


/*
 * Nurse creates requisition
 */
Route::post(
    '/api/requisitions',
    [SurgicalConsumableController::class, 'storeRequisition']
)->middleware('role:nurse,admin')
 ->name('api.sc1.requisitions.store');


/*
 * MS approves requisition
 */
Route::put(
    '/api/requisitions/{id}/approve',
    [SurgicalConsumableController::class, 'approveRequisition']
)->middleware('role:ms,admin')
 ->name('api.sc1.requisitions.approve');


/*
 * MS rejects requisition
 */
Route::put(
    '/api/requisitions/{id}/reject',
    [SurgicalConsumableController::class, 'rejectRequisition']
)->middleware('role:ms,admin')
 ->name('api.sc1.requisitions.reject');


/*
 * Nurse receives approved stock
 */
Route::put(
    '/api/requisitions/{id}/receive',
    [SurgicalConsumableController::class, 'receiveRequisition']
)->middleware('role:nurse,admin')
 ->name('api.sc1.requisitions.receive');


/*
 * General update / backward compatibility
 */
Route::put(
    '/api/requisitions/{id}',
    [SurgicalConsumableController::class, 'updateRequisition']
)->name('api.sc1.requisitions.update');


/*
 * Nurse can delete pending request.
 * Admin can also delete.
 */
Route::delete(
    '/api/requisitions/{id}',
    [SurgicalConsumableController::class, 'destroyRequisition']
)->middleware('role:nurse,admin')
 ->name('api.sc1.requisitions.destroy');


/*
|--------------------------------------------------------------------------
| SC1 - STOCK ADJUSTMENTS
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/adjustments',
    [SurgicalConsumableController::class, 'getAdjustments']
)->name('api.sc1.adjustments.index');

Route::post(
    '/api/adjustments',
    [SurgicalConsumableController::class, 'storeAdjustment']
)->middleware('role:admin')
 ->name('api.sc1.adjustments.store');

Route::delete(
    '/api/adjustments/{id}',
    [SurgicalConsumableController::class, 'destroyAdjustment']
)->middleware('role:admin')
 ->name('api.sc1.adjustments.destroy');


/*
|--------------------------------------------------------------------------
| SC1 - DAILY COUNTS
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/daily-counts',
    [SurgicalConsumableController::class, 'getDailyCounts']
)->name('api.sc1.daily-counts.index');

Route::post(
    '/api/daily-counts',
    [SurgicalConsumableController::class, 'storeDailyCount']
)->middleware('role:admin,nurse')
 ->name('api.sc1.daily-counts.store');


/*
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
| SURGICAL CONSUMABLE 2 API
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
|
| IMPORTANT:
| These routes are deliberately NOT /api/requisitions.
|
| Surgical Consumable 2 has its own controller and therefore its own
| API namespace.
|
*/


/*
|--------------------------------------------------------------------------
| SC2 - CONSUMABLE ITEMS
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/surgical-consumable-2/consumables',
    [SurgicalConsumable2Controller::class, 'getConsumables']
)->name('api.sc2.consumables.index');

Route::post(
    '/api/surgical-consumable-2/consumables',
    [SurgicalConsumable2Controller::class, 'storeConsumable']
)->middleware('role:admin')
 ->name('api.sc2.consumables.store');

Route::put(
    '/api/surgical-consumable-2/consumables/{id}',
    [SurgicalConsumable2Controller::class, 'updateConsumable']
)->middleware('role:admin')
 ->name('api.sc2.consumables.update');

Route::delete(
    '/api/surgical-consumable-2/consumables/{id}',
    [SurgicalConsumable2Controller::class, 'destroyConsumable']
)->middleware('role:admin')
 ->name('api.sc2.consumables.destroy');


/*
|--------------------------------------------------------------------------
| SC2 - REQUISITIONS
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/surgical-consumable-2/requisitions',
    [SurgicalConsumable2Controller::class, 'getRequisitions']
)->name('api.sc2.requisitions.index');


/*
 * Nurse creates request
 */
Route::post(
    '/api/surgical-consumable-2/requisitions',
    [SurgicalConsumable2Controller::class, 'storeRequisition']
)->middleware('role:nurse,admin')
 ->name('api.sc2.requisitions.store');


/*
 * MS approves request
 */
Route::put(
    '/api/surgical-consumable-2/requisitions/{id}/approve',
    [SurgicalConsumable2Controller::class, 'approveRequisition']
)->middleware('role:ms,admin')
 ->name('api.sc2.requisitions.approve');


/*
 * MS rejects request
 */
Route::put(
    '/api/surgical-consumable-2/requisitions/{id}/reject',
    [SurgicalConsumable2Controller::class, 'rejectRequisition']
)->middleware('role:ms,admin')
 ->name('api.sc2.requisitions.reject');


/*
 * Pharmacist dispenses
 */
Route::put(
    '/api/surgical-consumable-2/requisitions/{id}/dispense',
    [SurgicalConsumable2Controller::class, 'dispenseRequisition']
)->middleware('role:pharmacist,admin')
 ->name('api.sc2.requisitions.dispense');


/*
 * Nurse receives
 */
Route::put(
    '/api/surgical-consumable-2/requisitions/{id}/receive',
    [SurgicalConsumable2Controller::class, 'receiveRequisition']
)->middleware('role:nurse,admin')
 ->name('api.sc2.requisitions.receive');


/*
 * General update
 */
Route::put(
    '/api/surgical-consumable-2/requisitions/{id}',
    [SurgicalConsumable2Controller::class, 'updateRequisition']
)->name('api.sc2.requisitions.update');


/*
 * Requisition deletion
 *
 * The current SC2 controller's destroyRequisition method is written
 * as an admin deletion method.
 */
Route::delete(
    '/api/surgical-consumable-2/requisitions/{id}',
    [SurgicalConsumable2Controller::class, 'destroyRequisition']
)->middleware('role:admin')
 ->name('api.sc2.requisitions.destroy');


/*
|--------------------------------------------------------------------------
| SC2 - STOCK ADJUSTMENTS
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/surgical-consumable-2/adjustments',
    [SurgicalConsumable2Controller::class, 'getAdjustments']
)->name('api.sc2.adjustments.index');

Route::post(
    '/api/surgical-consumable-2/adjustments',
    [SurgicalConsumable2Controller::class, 'storeAdjustment']
)->middleware('role:admin')
 ->name('api.sc2.adjustments.store');

Route::delete(
    '/api/surgical-consumable-2/adjustments/{id}',
    [SurgicalConsumable2Controller::class, 'destroyAdjustment']
)->middleware('role:admin')
 ->name('api.sc2.adjustments.destroy');


/*
|--------------------------------------------------------------------------
| SC2 - DAILY COUNTS
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/surgical-consumable-2/daily-counts',
    [SurgicalConsumable2Controller::class, 'getDailyCounts']
)->name('api.sc2.daily-counts.index');

Route::post(
    '/api/surgical-consumable-2/daily-counts',
    [SurgicalConsumable2Controller::class, 'storeDailyCount']
)->middleware('role:admin,nurse')
 ->name('api.sc2.daily-counts.store');


/*
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
| LOCAL PURCHASES API
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
|
| Controller:
| App\Http\Controllers\LocalPurchaseController
|
*/


/*
|--------------------------------------------------------------------------
| LOCAL PURCHASE - ITEMS
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/local-purchases/items',
    [LocalPurchaseController::class, 'getItems']
)->name('api.local.items.index');

Route::post(
    '/api/local-purchases/items',
    [LocalPurchaseController::class, 'storeItem']
)->middleware('role:admin')
 ->name('api.local.items.store');

Route::put(
    '/api/local-purchases/items/{id}',
    [LocalPurchaseController::class, 'updateItem']
)->middleware('role:admin')
 ->name('api.local.items.update');

Route::delete(
    '/api/local-purchases/items/{id}',
    [LocalPurchaseController::class, 'destroyItem']
)->middleware('role:admin')
 ->name('api.local.items.destroy');


/*
|--------------------------------------------------------------------------
| LOCAL PURCHASE - REQUISITIONS
|--------------------------------------------------------------------------
*/

/*
 * Get Local Purchase requisitions
 */
Route::get(
    '/api/local-purchases/requisitions',
    [LocalPurchaseController::class, 'getRequisitions']
)->name('api.local.requisitions.index');


/*
 * Nurse creates Local Purchase request
 */
Route::post(
    '/api/local-purchases/requisitions',
    [LocalPurchaseController::class, 'storeRequisition']
)->middleware('role:nurse,admin')
 ->name('api.local.requisitions.store');


/*
 * MS approves Local Purchase request
 */
Route::put(
    '/api/local-purchases/requisitions/{id}/approve',
    [LocalPurchaseController::class, 'approveRequisition']
)->middleware('role:ms,admin')
 ->name('api.local.requisitions.approve');


/*
 * MS rejects Local Purchase request
 */
Route::put(
    '/api/local-purchases/requisitions/{id}/reject',
    [LocalPurchaseController::class, 'rejectRequisition']
)->middleware('role:ms,admin')
 ->name('api.local.requisitions.reject');


/*
 * Nurse records actual received quantity.
 *
 * Stock is updated only when this endpoint is called.
 */
Route::put(
    '/api/local-purchases/requisitions/{id}/receive',
    [LocalPurchaseController::class, 'receiveRequisition']
)->middleware('role:nurse,admin')
 ->name('api.local.requisitions.receive');


/*
 * General update / backward compatibility
 */
Route::put(
    '/api/local-purchases/requisitions/{id}',
    [LocalPurchaseController::class, 'updateRequisition']
)->name('api.local.requisitions.update');


/*
 * Delete pending Local Purchase request.
 */
Route::delete(
    '/api/local-purchases/requisitions/{id}',
    [LocalPurchaseController::class, 'destroyRequisition']
)->middleware('role:nurse,admin')
 ->name('api.local.requisitions.destroy');


/*
|--------------------------------------------------------------------------
| LOCAL PURCHASE - STOCK ADJUSTMENTS
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/local-purchases/adjustments',
    [LocalPurchaseController::class, 'getAdjustments']
)->name('api.local.adjustments.index');

Route::post(
    '/api/local-purchases/adjustments',
    [LocalPurchaseController::class, 'storeAdjustment']
)->middleware('role:admin')
 ->name('api.local.adjustments.store');

Route::delete(
    '/api/local-purchases/adjustments/{id}',
    [LocalPurchaseController::class, 'destroyAdjustment']
)->middleware('role:admin')
 ->name('api.local.adjustments.destroy');


/*
|--------------------------------------------------------------------------
| LOCAL PURCHASE - DAILY COUNTS
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/local-purchases/daily-counts',
    [LocalPurchaseController::class, 'getDailyCounts']
)->name('api.local.daily-counts.index');

Route::post(
    '/api/local-purchases/daily-counts',
    [LocalPurchaseController::class, 'storeDailyCount']
)->middleware('role:admin,nurse')
 ->name('api.local.daily-counts.store');

 Route::put(
    '/api/local-purchases/requisitions/{id}/approve',
    [LocalPurchaseController::class, 'approveRequisition']
)->middleware('role:ms,admin');

Route::put(
    '/api/local-purchases/requisitions/{id}/reject',
    [LocalPurchaseController::class, 'rejectRequisition']
)->middleware('role:ms,admin');

Route::put(
    '/api/local-purchases/requisitions/{id}/receive',
    [LocalPurchaseController::class, 'receiveRequisition']
)->middleware('role:nurse,admin');