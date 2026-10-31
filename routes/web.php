<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Http\Controllers\SurgicalConsumableController;
use App\Http\Controllers\SurgicalConsumable2Controller;
use App\Http\Controllers\LocalPurchaseController;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\DispensationController;
use App\Http\Controllers\GeneralItemController;
use App\Http\Controllers\GeneralStockAdjustmentController;
use App\Http\Controllers\GeneralTransactionController;
use App\Http\Controllers\FormTypeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;


/*
|--------------------------------------------------------------------------
| HOME / PORTAL
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('home');
});

Route::get('/home', function () {
    return view('home');
})->name('home');


/*
|--------------------------------------------------------------------------
| AUTHENTICATION
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLoginForm'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.submit');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


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


/*
|--------------------------------------------------------------------------
| PHARMACIST SC2 VIEW
|--------------------------------------------------------------------------
|
| Pharmacist can view Surgical Consumables 2.
| Pharmacist does not approve, reject, receive, or dispense.
|
*/

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
| /nurse      -> /nurse/surgical-consumable-1
| /ms         -> /ms/surgical-consumable-1
| /admin      -> /admin/surgical-consumable-1
| /pharmacist -> /pharmacist/surgical-consumable-1
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
    ->where('role', 'nurse|ms|admin|pharmacist')
    ->middleware('auth');


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
    ->middleware(['auth', 'role:nurse,admin'])
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


/*
|--------------------------------------------------------------------------
| IMPORTANT:
| No pharmacist dispense route here.
|
| SC2 follows the same procedure as SC1:
|
| Nurse -> MS approval/rejection -> Nurse receives
|
| Pharmacist has a view only.
|--------------------------------------------------------------------------
*/

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
    ->middleware(['auth', 'role:nurse,admin'])
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
    ->middleware(['auth', 'role:nurse,admin'])
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
    ->middleware(['auth', 'role:nurse,admin'])
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


/*
|--------------------------------------------------------------------------
| AUTHENTICATED INVENTORY SYSTEM ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | General Inventory
    |--------------------------------------------------------------------------
    */

    Route::prefix('general-inventory')
        ->name('general-inventory.')
        ->group(function () {

            Route::get(
                '/',
                [GeneralItemController::class, 'index']
            )->name('index');

            Route::middleware(['role:Nurse,Head Nurse'])->group(function () {

                Route::post(
                    '/',
                    [GeneralItemController::class, 'store']
                )->name('store');

                Route::put(
                    '/{general_item}',
                    [GeneralItemController::class, 'update']
                )->name('update');

                Route::delete(
                    '/{general_item}',
                    [GeneralItemController::class, 'destroy']
                )->name('destroy');

                Route::post(
                    '/{general_item}/transactions',
                    [GeneralTransactionController::class, 'store']
                )->name('transactions.store');

                Route::post(
                    '/{general_item}/adjustments',
                    [GeneralStockAdjustmentController::class, 'store']
                )->name('adjustments.store');
            });
        });


    /*
    |--------------------------------------------------------------------------
    | Medicines Inventory
    |--------------------------------------------------------------------------
    */

    Route::prefix('inventory/{category}')
        ->name('inventory.')
        ->group(function () {

            Route::get(
                '/',
                [MedicineController::class, 'index']
            )->name('index');

            Route::post(
                '/store',
                [MedicineController::class, 'storeMedicine']
            )->name('store');

            Route::get(
                '/{id}/details',
                [MedicineController::class, 'getDetails']
            )->name('details');
        });

    Route::post(
        '/inventory/medicines/administration/{category}/{id}',
        [MedicineController::class, 'storeAdministration']
    )->name('medicines.administration.store');

    Route::post(
        '/inventory/medicines/{category}/{id}/adjustments',
        [MedicineController::class, 'storeAdjustment']
    )->name('inventory.medicines.adjustment.store');

    Route::post(
        '/inventory/medicines/{category}/{id}/orders',
        [MedicineController::class, 'storeOrder']
    )->name('inventory.medicines.orders.store');

    Route::patch(
        '/inventory/medicines/{category}/orders/{id}/approve',
        [MedicineController::class, 'approveOrder']
    )->name('inventory.medicines.orders.approve');

    Route::patch(
        '/inventory/medicines/{category}/orders/{id}/issue',
        [MedicineController::class, 'issueOrder']
    )->name('inventory.medicines.orders.issue');

    Route::post(
        '/inventory/medicines/{category}/orders/{id}/receive',
        [MedicineController::class, 'receiveOrder']
    )->name('inventory.medicines.orders.receive');

    Route::match(
        ['patch', 'post'],
        '/inventory/medicines/{category}/orders/{id}/cancel',
        [MedicineController::class, 'cancelOrder']
    )->name('inventory.medicines.orders.cancel');

    Route::put(
        '/inventory/medicines/{id}',
        [MedicineController::class, 'update']
    );

    Route::delete(
        '/inventory/medicines/{id}',
        [MedicineController::class, 'destroy']
    );

    Route::post(
        '/inventory/medicines/expire-batches',
        [MedicineController::class, 'expireBatches']
    )->name('inventory.medicines.expire-batches');


    /*
    |--------------------------------------------------------------------------
    | Patients & Narcotic Dispensing
    |--------------------------------------------------------------------------
    */

    Route::prefix('patients')
        ->name('patients.')
        ->group(function () {

            Route::get(
                '/',
                [PatientController::class, 'index']
            )->name('index');

            Route::get(
                '/{patient}',
                [PatientController::class, 'show']
            )->name('show');

            Route::middleware(['role:Nurse,Head Nurse'])->group(function () {

                Route::post(
                    '/',
                    [PatientController::class, 'store']
                )->name('store');

                Route::post(
                    '/{patient}/admissions',
                    [AdmissionController::class, 'store']
                )->name('admissions.store');
            });
        });


    Route::middleware(['role:Nurse,Head Nurse'])->group(function () {

        Route::put(
            '/admissions/{admission}',
            [AdmissionController::class, 'update']
        )->name('admissions.update');

        Route::post(
            '/admissions/{admission}/dispensations',
            [DispensationController::class, 'store']
        )->name('admissions.dispensations.store');
    });


    /*
    |--------------------------------------------------------------------------
    | Centralized Orders Dashboard
    |--------------------------------------------------------------------------
    */

    Route::prefix('orders')
        ->name('orders.')
        ->group(function () {

            Route::get(
                '/',
                [OrderController::class, 'index']
            )->name('index');

            Route::patch(
                '/approve-all',
                [OrderController::class, 'approveAll']
            )->name('approveAll');

            Route::patch(
                '/{id}/approve',
                [OrderController::class, 'approve']
            )->name('approve');

            Route::patch(
                '/{id}/issue',
                [OrderController::class, 'issue']
            )->name('issue');

            Route::post(
                '/{id}/receive',
                [OrderController::class, 'receive']
            )->name('receive');

            Route::match(
                ['patch', 'post'],
                '/{id}/cancel',
                [OrderController::class, 'cancel']
            )->name('cancel');
        });


    /*
    |--------------------------------------------------------------------------
    | Master Data
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'role:Admin,Head Nurse,Nurse,Pharmacist,Doctor'
    ])->group(function () {

        Route::get(
            '/form-types',
            [FormTypeController::class, 'index']
        )->name('form-types.index');

        Route::get(
            '/units',
            [UnitController::class, 'index']
        )->name('units.index');

        Route::get(
            '/suppliers',
            [SupplierController::class, 'index']
        )->name('suppliers.index');
    });

    Route::middleware([
        'role:Admin,Head Nurse,Nurse,Pharmacist'
    ])->group(function () {

        Route::resource(
            'form-types',
            FormTypeController::class
        )->except(['index']);

        Route::resource(
            'units',
            UnitController::class
        )->except(['index']);

        Route::resource(
            'suppliers',
            SupplierController::class
        )->except(['index']);
    });
});


/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/

Route::get(
    '/dashboard',
    function () {
        return view('dashboard');
    }
)->middleware('auth')->name('dashboard');


/*
|--------------------------------------------------------------------------
| TEST PAGE
|--------------------------------------------------------------------------
*/

Route::get(
    '/test',
    function () {
        return view('test');
    }
)->name('test');


/*
|--------------------------------------------------------------------------
| USER ROUTES
|--------------------------------------------------------------------------
*/

Route::get(
    '/users',
    [UserController::class, 'index']
)->name('getAllUsers');

Route::get(
    '/users/create',
    [UserController::class, 'create']
)->name('createUser');

Route::post(
    '/users',
    [UserController::class, 'store']
)->name('storeUser');

Route::get(
    '/users/{id}',
    [UserController::class, 'show']
)->name('getUser');