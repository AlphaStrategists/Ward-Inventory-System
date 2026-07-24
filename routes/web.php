<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GInventoryController;
use App\Http\Controllers\PatientController;

Route::get('/', [UserController::class, 'login'])-> name('login');
Route::get('/home', [UserController::class, 'home'])->name('home');
Route::get('/password/reset', [UserController::class, 'showPasswordResetForm'])->name('password.request');

Route::get('/ginventory', [GInventoryController::class, 'view']);

Route::get('/ginventory/index', [GInventoryController::class, 'index'])->name('inventory.index');
Route::post('/ginventory/store', [GInventoryController::class, 'store'])->name('inventory.store');
Route::put('/ginventory/update/{inventoryItem}', [GInventoryController::class, 'update'])->name('inventory.update');
Route::delete('/ginventory/destroy/{inventoryItem}', [GInventoryController::class, 'destroy'])->name('inventory.destroy');

Route::get('/patients', [PatientController::class, 'index']);
Route::post('/patients/store', [PatientController::class, 'store']);
Route::put('/patients/update/{patient}', [PatientController::class, 'update']);
Route::delete('/patients/destroy/{patient}', [PatientController::class, 'destroy']);


