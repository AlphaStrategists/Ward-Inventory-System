<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GInventoryController;
use App\Http\Controllers\PatientController;



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

Route::get('/', [UserController::class, 'login'])-> name('login');
Route::get('/home', [UserController::class, 'home'])->name('home');
Route::get('/password/reset', [UserController::class, 'showPasswordResetForm'])->name('password.request');
