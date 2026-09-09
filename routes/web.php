<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GInventoryController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\NarcoticController;
use App\Http\Controllers\SyrupController;
use App\Http\Controllers\IvFluidController;
use App\Http\Controllers\OralCountableController;
use App\Http\Controllers\AntibioticController;
use App\Http\Controllers\OralAntibioticController;          


Route::get('/inventory', function () {
    return view('Pages.GenInventory');
});
Route::get('/inventory', [GInventoryController::class, 'index'])->name('inventory.index');
Route::post('/inventory', [GInventoryController::class, 'store'])->name('inventory.store');
Route::put('/inventory/{inventoryItem}', [GInventoryController::class, 'update'])->name('inventory.update');
Route::delete('/inventory/{inventoryItem}', [GInventoryController::class, 'destroy'])->name('inventory.destroy');

Route::get('/patient', [PatientController::class, 'index']);

Route::get('/patients', [PatientController::class, 'index']);
Route::post('/patients', [PatientController::class, 'store']);
Route::put('/patients/{patient}', [PatientController::class, 'update']);
Route::delete('/patients/{patient}', [PatientController::class, 'destroy']);


//Route::get('/', [Controller::class, 'index']);
Route::get('/narcotics', [NarcoticController::class, 'index'])->name('narcotics.index');
Route::get('/syrup', [SyrupController::class, 'index'])->name('syrup.index');
Route::get('/iv-fluid', [IvFluidController::class, 'index'])->name('iv-fluid.index');
Route::get('/oral-countable', [OralCountableController::class, 'index'])->name('oral-countable.index');
Route::get('/antibiotic', [AntibioticController::class, 'index'])->name('antibiotic.index');
Route::get('/oral-antibiotic', [OralAntibioticController::class, 'index'])->name('oral-antibiotic.index');
>>>>>>> Stashed changes

Route::get('/', function () {
    return view('welcome');
});

Route::get('/inj', function () {
    $drug = (object) [
        'name' => 'Diazepam',
        'stock_balance' => 85,
        'unit' => 'tabs',
    ];

    return view('pages.Injection', ['drug' => $drug]);
});
Route::get('/injA', function () {
  
       

    return view('pages.InjectionAntibiotic');
});
Route::get('/', [UserController::class, 'login'])-> name('login');
Route::get('/home', [UserController::class, 'home'])->name('home');
Route::get('/password/reset', [UserController::class, 'showPasswordResetForm'])->name('password.request');
