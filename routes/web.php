<?php

use App\Http\Controllers\InjectionAntibioticController;
use App\Http\Controllers\InjectionController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\MsApprovalController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


// Injection Inventory Page
Route::get('/injections', [InjectionController::class, 'index'])->name('injections.index');

// Injection Antibiotics Inventory Page
Route::get('/injection-antibiotics', [InjectionAntibioticController::class, 'index'])->name('injection-antibiotics.index');

// Medical Superintendent (MS) Approval Portal Page
Route::get('/ms-approval', [MsApprovalController::class, 'index'])->name('ms.index');

// Order Requisition CRUD
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
