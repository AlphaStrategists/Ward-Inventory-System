<?php

use Illuminate\Support\Facades\Route;

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
use App\Http\Controllers\StrengthUnitController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;


/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (the ONLY routes that do not require login)
|--------------------------------------------------------------------------
*/

Route::get('/', [AuthController::class, 'showLoginForm'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.submit');


/*
|--------------------------------------------------------------------------
| EVERYTHING BELOW REQUIRES LOGIN
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | HOME / DASHBOARD / LOGOUT
    |--------------------------------------------------------------------------
    */

    Route::get('/home', function () {
        return view('home');
    })->name('home');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');


    /*
    |--------------------------------------------------------------------------
    | ROLE-SPECIFIC PAGE ROUTES
    |--------------------------------------------------------------------------
    | URL prefix => role name passed to the role middleware.
    | (Kept exactly as in the original file.)
    */

    $pageRoles = [
        'nurse'      => 'Nurse',
        'ms'         => 'ms',
        'admin'      => 'admin',
        'pharmacist' => 'pharmacist',
    ];

    // Surgical Consumable 1 + Local Purchases: all four roles
    foreach ($pageRoles as $prefix => $role) {

        Route::get(
            "/{$prefix}/surgical-consumable-1",
            [SurgicalConsumableController::class, 'index']
        )
            ->defaults('role', $role)
            ->middleware("role:{$role}")
            ->name("{$prefix}.surgical-consumable-1");

        Route::get(
            "/{$prefix}/surgical-consumable-2",
            [SurgicalConsumable2Controller::class, 'index']
        )
            ->defaults('role', $role)
            ->middleware("role:{$role}")
            ->name("{$prefix}.surgical-consumable-2");

        Route::get(
            "/{$prefix}/local-purchases",
            [LocalPurchaseController::class, 'index']
        )
            ->defaults('role', $role)
            ->middleware("role:{$role}")
            ->name("{$prefix}.local-purchases");
    }

    /*
    | NOTE: Pharmacist can only VIEW Surgical Consumables 2.
    | Pharmacist does not approve, reject, receive, or dispense.
    */


    /*
    |--------------------------------------------------------------------------
    | ROLE ROOT REDIRECT
    |--------------------------------------------------------------------------
    | /Nurse      -> /nurse/surgical-consumable-1
    | /Ms         -> /ms/surgical-consumable-1
    | /Pharmacist -> /pharmacist/surgical-consumable-1
    |
    | Fixed: the old version redirected to capitalised URLs (/Ms/...) that
    | do not exist, which caused a 404. Roles that have no pages (HeadNurse,
    | Doctor) now return 404 instead of redirecting to a missing page.
    */

    Route::get('/{role}', function ($role) {

        $map = [
            'Nurse'      => 'nurse',
            'Ms'         => 'ms',
            'Pharmacist' => 'pharmacist',
            'Doctor' => 'doctor',
            'HeadNurse' => 'head_nurse',
        ];

        if (!array_key_exists($role, $map)) {
            abort(404);
        }

        return redirect("/{$map[$role]}/surgical-consumable-1");
    })->where('role', 'Nurse|Ms|HeadNurse|Pharmacist|Doctor');


    /*
    |--------------------------------------------------------------------------
    | SURGICAL CONSUMABLE 1 API
    |--------------------------------------------------------------------------
    */

    // SC1 - Consumable items
    Route::get('/api/consumables', [SurgicalConsumableController::class, 'getConsumables'])
        ->name('api.sc1.consumables.index');

    Route::post('/api/consumables', [SurgicalConsumableController::class, 'storeConsumable'])
        ->middleware('role:Admin')
        ->name('api.sc1.consumables.store');

    Route::put('/api/consumables/{id}', [SurgicalConsumableController::class, 'updateConsumable'])
        ->middleware('role:Head Nurse')
        ->name('api.sc1.consumables.update');

    Route::delete('/api/consumables/{id}', [SurgicalConsumableController::class, 'destroyConsumable'])
        ->middleware('role:Admin')
        ->name('api.sc1.consumables.destroy');

    // SC1 - Requisitions
    Route::get('/api/requisitions', [SurgicalConsumableController::class, 'getRequisitions'])
        ->name('api.sc1.requisitions.index');

    Route::post('/api/requisitions', [SurgicalConsumableController::class, 'storeRequisition'])
        ->middleware('role:Nurse')
        ->name('api.sc1.requisitions.store');

    Route::put('/api/requisitions/{id}/approve', [SurgicalConsumableController::class, 'approveRequisition'])
        ->middleware('role:Ms')
        ->name('api.sc1.requisitions.approve');

    Route::put('/api/requisitions/{id}/reject', [SurgicalConsumableController::class, 'rejectRequisition'])
        ->middleware('role:Ms')
        ->name('api.sc1.requisitions.reject');

    Route::put('/api/requisitions/{id}/receive', [SurgicalConsumableController::class, 'receiveRequisition'])
        ->middleware('role:Head Nurse,Nurse')
        ->name('api.sc1.requisitions.receive');

    // FIXED: previously only "auth" - now restricted to nursing roles
    Route::put('/api/requisitions/{id}', [SurgicalConsumableController::class, 'updateRequisition'])
        ->middleware('role:Head Nurse,Nurse')
        ->name('api.sc1.requisitions.update');

    Route::delete('/api/requisitions/{id}', [SurgicalConsumableController::class, 'destroyRequisition'])
        ->middleware('role:Head Nurse,Nurse')
        ->name('api.sc1.requisitions.destroy');

    // SC1 - Stock adjustments
    Route::get('/api/adjustments', [SurgicalConsumableController::class, 'getAdjustments'])
        ->name('api.sc1.adjustments.index');

    Route::post('/api/adjustments', [SurgicalConsumableController::class, 'storeAdjustment'])
        ->middleware('role:Nurse,Head Nurse')
        ->name('api.sc1.adjustments.store');

    Route::delete('/api/adjustments/{id}', [SurgicalConsumableController::class, 'destroyAdjustment'])
        ->middleware('role:Nurse,Head Nurse')
        ->name('api.sc1.adjustments.destroy');

    // SC1 - Daily counts
    Route::get('/api/daily-counts', [SurgicalConsumableController::class, 'getDailyCounts'])
        ->name('api.sc1.daily-counts.index');

    Route::post('/api/daily-counts', [SurgicalConsumableController::class, 'storeDailyCount'])
        ->middleware('role:Nurse,Head Nurse')
        ->name('api.sc1.daily-counts.store');


    /*
    |--------------------------------------------------------------------------
    | SURGICAL CONSUMABLE 2 API
    |--------------------------------------------------------------------------
    | SC2 follows the same procedure as SC1:
    | Nurse -> MS approval/rejection -> Nurse receives.
    | No pharmacist dispense route. Pharmacist has view only.
    */

    // SC2 - Consumable items
    Route::get('/api/surgical-consumable-2/consumables', [SurgicalConsumable2Controller::class, 'getConsumables'])
        ->name('api.sc2.consumables.index');

    Route::post('/api/surgical-consumable-2/consumables', [SurgicalConsumable2Controller::class, 'storeConsumable'])
        ->middleware('role:Admin')
        ->name('api.sc2.consumables.store');

    Route::put('/api/surgical-consumable-2/consumables/{id}', [SurgicalConsumable2Controller::class, 'updateConsumable'])
        ->middleware('role:Admin')
        ->name('api.sc2.consumables.update');

    Route::delete('/api/surgical-consumable-2/consumables/{id}', [SurgicalConsumable2Controller::class, 'destroyConsumable'])
        ->middleware('role:Admin')
        ->name('api.sc2.consumables.destroy');

    // SC2 - Requisitions
    Route::get('/api/surgical-consumable-2/requisitions', [SurgicalConsumable2Controller::class, 'getRequisitions'])
        ->name('api.sc2.requisitions.index');

    Route::post('/api/surgical-consumable-2/requisitions', [SurgicalConsumable2Controller::class, 'storeRequisition'])
        ->middleware('role:Head Nurse,Nurse')
        ->name('api.sc2.requisitions.store');

    Route::put('/api/surgical-consumable-2/requisitions/{id}/approve', [SurgicalConsumable2Controller::class, 'approveRequisition'])
        ->middleware('role:Ms')
        ->name('api.sc2.requisitions.approve');

    Route::put('/api/surgical-consumable-2/requisitions/{id}/reject', [SurgicalConsumable2Controller::class, 'rejectRequisition'])
        ->middleware('role:Ms')
        ->name('api.sc2.requisitions.reject');

    Route::put('/api/surgical-consumable-2/requisitions/{id}/receive', [SurgicalConsumable2Controller::class, 'receiveRequisition'])
        ->middleware('role:Nurse,Head Nurse')
        ->name('api.sc2.requisitions.receive');

    // FIXED: previously only "auth"
    Route::put('/api/surgical-consumable-2/requisitions/{id}', [SurgicalConsumable2Controller::class, 'updateRequisition'])
        ->middleware('role:Head Nurse,Nurse')
        ->name('api.sc2.requisitions.update');

    Route::delete('/api/surgical-consumable-2/requisitions/{id}', [SurgicalConsumable2Controller::class, 'destroyRequisition'])
        ->middleware('role:Nurse,Head Nurse')
        ->name('api.sc2.requisitions.destroy');

    // SC2 - Stock adjustments
    Route::get('/api/surgical-consumable-2/adjustments', [SurgicalConsumable2Controller::class, 'getAdjustments'])
        ->name('api.sc2.adjustments.index');

    Route::post('/api/surgical-consumable-2/adjustments', [SurgicalConsumable2Controller::class, 'storeAdjustment'])
        ->middleware('role:Head Nurse,Nurse')
        ->name('api.sc2.adjustments.store');

    Route::delete('/api/surgical-consumable-2/adjustments/{id}', [SurgicalConsumable2Controller::class, 'destroyAdjustment'])
        ->middleware('role:Head Nurse,Nurse')
        ->name('api.sc2.adjustments.destroy');

    // SC2 - Daily counts
    Route::get('/api/surgical-consumable-2/daily-counts', [SurgicalConsumable2Controller::class, 'getDailyCounts'])
        ->name('api.sc2.daily-counts.index');

    Route::post('/api/surgical-consumable-2/daily-counts', [SurgicalConsumable2Controller::class, 'storeDailyCount'])
        ->middleware('role:Head Nurse,Nurse')
        ->name('api.sc2.daily-counts.store');


    /*
    |--------------------------------------------------------------------------
    | LOCAL PURCHASES API
    |--------------------------------------------------------------------------
    */

    // Local purchase - Items
    Route::get('/api/local-purchases/items', [LocalPurchaseController::class, 'getItems'])
        ->name('api.local.items.index');

    Route::post('/api/local-purchases/items', [LocalPurchaseController::class, 'storeItem'])
        ->middleware('role:Admin')
        ->name('api.local.items.store');

    Route::put('/api/local-purchases/items/{id}', [LocalPurchaseController::class, 'updateItem'])
        ->middleware('role:Admin')
        ->name('api.local.items.update');

    Route::delete('/api/local-purchases/items/{id}', [LocalPurchaseController::class, 'destroyItem'])
        ->middleware('role:Admin')
        ->name('api.local.items.destroy');

    // Local purchase - Requisitions
    Route::get('/api/local-purchases/requisitions', [LocalPurchaseController::class, 'getRequisitions'])
        ->name('api.local.requisitions.index');

    Route::post('/api/local-purchases/requisitions', [LocalPurchaseController::class, 'storeRequisition'])
        ->middleware('role:Head Nurse,Nurse')
        ->name('api.local.requisitions.store');

    Route::put('/api/local-purchases/requisitions/{id}/approve', [LocalPurchaseController::class, 'approveRequisition'])
        ->middleware('role:Ms')
        ->name('api.local.requisitions.approve');

    Route::put('/api/local-purchases/requisitions/{id}/reject', [LocalPurchaseController::class, 'rejectRequisition'])
        ->middleware('role:Ms')
        ->name('api.local.requisitions.reject');

    Route::put('/api/local-purchases/requisitions/{id}/receive', [LocalPurchaseController::class, 'receiveRequisition'])
        ->middleware('role:Head Nurse,Nurse')
        ->name('api.local.requisitions.receive');

    // FIXED: previously only "auth"
    Route::put('/api/local-purchases/requisitions/{id}', [LocalPurchaseController::class, 'updateRequisition'])
        ->middleware('role:Head Nurse,Nurse')
        ->name('api.local.requisitions.update');

    Route::delete('/api/local-purchases/requisitions/{id}', [LocalPurchaseController::class, 'destroyRequisition'])
        ->middleware('role:Nurse,admin')
        ->name('api.local.requisitions.destroy');

    // Local purchase - Stock adjustments
    Route::get('/api/local-purchases/adjustments', [LocalPurchaseController::class, 'getAdjustments'])
        ->name('api.local.adjustments.index');

    Route::post('/api/local-purchases/adjustments', [LocalPurchaseController::class, 'storeAdjustment'])
        ->middleware('role:Nurse,admin')
        ->name('api.local.adjustments.store');

    Route::delete('/api/local-purchases/adjustments/{id}', [LocalPurchaseController::class, 'destroyAdjustment'])
        ->middleware('role:Nurse,admin')
        ->name('api.local.adjustments.destroy');

    // Local purchase - Daily counts
    Route::get('/api/local-purchases/daily-counts', [LocalPurchaseController::class, 'getDailyCounts'])
        ->name('api.local.daily-counts.index');

    Route::post('/api/local-purchases/daily-counts', [LocalPurchaseController::class, 'storeDailyCount'])
        ->middleware('role:admin,Nurse')
        ->name('api.local.daily-counts.store');


    /*
    |--------------------------------------------------------------------------
    | GENERAL INVENTORY
    |--------------------------------------------------------------------------
    */

    Route::prefix('general-inventory')
        ->name('general-inventory.')
        ->group(function () {

            Route::get('/', [GeneralItemController::class, 'index'])->name('index');

            Route::middleware('role:Nurse,Head Nurse')->group(function () {

                Route::post('/', [GeneralItemController::class, 'store'])->name('store');

                Route::put('/{general_item}', [GeneralItemController::class, 'update'])->name('update');

                Route::delete('/{general_item}', [GeneralItemController::class, 'destroy'])->name('destroy');

                Route::post('/{general_item}/transactions', [GeneralTransactionController::class, 'store'])
                    ->name('transactions.store');

                Route::post('/{general_item}/adjustments', [GeneralStockAdjustmentController::class, 'store'])
                    ->name('adjustments.store');
            });
        });


    /*
    |--------------------------------------------------------------------------
    | MEDICINES INVENTORY
    |--------------------------------------------------------------------------
    | ASSUMED ROLES - adjust the role lists below to match your hospital's
    | real workflow:
    |   - add / edit medicine ........ Admin, Pharmacist
    |   - delete medicine ............ Admin
    |   - administer / adjust / order  Nurse, Head Nurse
    |   - approve order .............. Ms
    |   - issue order ............... Pharmacist
    |   - receive order ............. Nurse, Head Nurse
    |   - cancel order ............. Nurse, Head Nurse, Ms
    |   - expire batches ........... Admin, Pharmacist
    */

    Route::prefix('inventory/{category}')
        ->name('inventory.')
        ->group(function () {

            Route::get('/', [MedicineController::class, 'index'])->name('index');

            // FIXED: previously any logged-in user could add medicines
            Route::post('/store', [MedicineController::class, 'storeMedicine'])
                ->middleware('role:Admin,Pharmacist')
                ->name('store');

            Route::get('/{id}/details', [MedicineController::class, 'getDetails'])->name('details');
        });

    Route::post('/inventory/medicines/administration/{category}/{id}', [MedicineController::class, 'storeAdministration'])
        ->middleware('role:Nurse,Head Nurse')
        ->name('medicines.administration.store');

    Route::post('/inventory/medicines/{category}/{id}/adjustments', [MedicineController::class, 'storeAdjustment'])
        ->middleware('role:Nurse,Head Nurse')
        ->name('inventory.medicines.adjustment.store');

    Route::post('/inventory/medicines/{category}/{id}/orders', [MedicineController::class, 'storeOrder'])
        ->middleware('role:Nurse,Head Nurse')
        ->name('inventory.medicines.orders.store');

    Route::patch('/inventory/medicines/{category}/orders/{id}/approve', [MedicineController::class, 'approveOrder'])
        ->middleware('role:Ms')
        ->name('inventory.medicines.orders.approve');

    Route::patch('/inventory/medicines/{category}/orders/{id}/issue', [MedicineController::class, 'issueOrder'])
        ->middleware('role:Pharmacist')
        ->name('inventory.medicines.orders.issue');

    Route::post('/inventory/medicines/{category}/orders/{id}/receive', [MedicineController::class, 'receiveOrder'])
        ->middleware('role:Nurse,Head Nurse')
        ->name('inventory.medicines.orders.receive');

    Route::match(['patch', 'post'], '/inventory/medicines/{category}/orders/{id}/cancel', [MedicineController::class, 'cancelOrder'])
        ->middleware('role:Nurse,Head Nurse,Ms')
        ->name('inventory.medicines.orders.cancel');

    Route::put('/inventory/medicines/{id}', [MedicineController::class, 'update'])
        ->middleware('role:Admin,Pharmacist');

    Route::delete('/inventory/medicines/{id}', [MedicineController::class, 'destroy'])
        ->middleware('role:Admin');

    Route::post('/inventory/medicines/expire-batches', [MedicineController::class, 'expireBatches'])
        ->middleware('role:Admin,Pharmacist')
        ->name('inventory.medicines.expire-batches');


    /*
    |--------------------------------------------------------------------------
    | PATIENTS & NARCOTIC DISPENSING
    |--------------------------------------------------------------------------
    */

    Route::prefix('patients')
        ->name('patients.')
        ->group(function () {

            Route::get('/', [PatientController::class, 'index'])->name('index');

            Route::get('/{patient}', [PatientController::class, 'show'])->name('show');

            Route::middleware('role:Nurse,Head Nurse')->group(function () {

                Route::post('/', [PatientController::class, 'store'])->name('store');

                Route::post('/{patient}/admissions', [AdmissionController::class, 'store'])
                    ->name('admissions.store');
            });
        });

    Route::middleware('role:Nurse,Head Nurse')->group(function () {

        Route::put('/admissions/{admission}', [AdmissionController::class, 'update'])
            ->name('admissions.update');

        Route::post('/admissions/{admission}/dispensations', [DispensationController::class, 'store'])
            ->name('admissions.dispensations.store');
    });


    /*
    |--------------------------------------------------------------------------
    | CENTRALIZED ORDERS DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::prefix('orders')
        ->name('orders.')
        ->group(function () {

            Route::get('/', [OrderController::class, 'index'])->name('index');

            Route::patch('/approve-all', [OrderController::class, 'approveAll'])
                ->middleware('role:Ms')
                ->name('approveAll');

            Route::patch('/{id}/approve', [OrderController::class, 'approve'])
                ->middleware('role:Ms')
                ->name('approve');

            Route::patch('/{id}/issue', [OrderController::class, 'issue'])
                ->middleware('role:Pharmacist')
                ->name('issue');

            Route::post('/{id}/receive', [OrderController::class, 'receive'])
                ->middleware('role:Nurse,Head Nurse')
                ->name('receive');

            Route::match(['patch', 'post'], '/{id}/cancel', [OrderController::class, 'cancel'])
                ->middleware('role:Nurse,Head Nurse,Ms')
                ->name('cancel');
        });


    /*
    |--------------------------------------------------------------------------
    | MASTER DATA (Admin only)
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:Admin')->group(function () {

        Route::resource('form-types', FormTypeController::class);

        Route::resource('units', UnitController::class);

        Route::resource('strength-units', StrengthUnitController::class)
            ->except(['create', 'show', 'edit']);

        Route::resource('suppliers', SupplierController::class);
    });


    /*
    |--------------------------------------------------------------------------
    | USER MANAGEMENT (Admin only)
    |--------------------------------------------------------------------------
    | FIXED: these routes had no login check at all, so anyone could list
    | users or create an account.
    */

    Route::middleware('role:Admin')
        ->prefix('users')
        ->group(function () {

            Route::get('/', [UserController::class, 'index'])->name('getAllUsers');

            Route::get('/create', [UserController::class, 'create'])->name('createUser');

            Route::post('/', [UserController::class, 'store'])->name('storeUser');

            Route::get('/{id}', [UserController::class, 'show'])->name('getUser');
        });
});

/*
|--------------------------------------------------------------------------
| NOTE: The old /test route was removed. If you still need it during
| development, add it back inside the auth group above.
|--------------------------------------------------------------------------
*/