<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GInventoryController;
use App\Http\Controllers\PatientController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/inventory', function () {
    return view('Pages.GenInventory');
});
Route::get('/inventory', [GInventoryController::class, 'index'])->name('inventory.index');
Route::post('/inventory', [GInventoryController::class, 'store'])->name('inventory.store');
Route::put('/inventory/{inventoryItem}', [GInventoryController::class, 'update'])->name('inventory.update');
Route::delete('/inventory/{inventoryItem}', [GInventoryController::class, 'destroy'])->name('inventory.destroy');

Route::get('/patient', function () {
    return view('Pages.PatientList');
});


Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');
Route::post('/patients', [PatientController::class, 'store'])->name('patients.store');
Route::put('/patients/{patient}', [PatientController::class, 'update'])->name('patients.update');
Route::delete('/patients/{patient}', [PatientController::class, 'destroy'])->name('patients.destroy');