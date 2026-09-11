<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MedicineController;

Route::get('/test', function () {
    return "Laravel is working perfectly!";
});

Route::get('/', function () {
    return redirect()->route('inventory.index', ['category' => 'antibiotics']);
});

Route::prefix('inventory/{category}')->name('inventory.')->group(function () {
    Route::get('/', [MedicineController::class, 'index'])->name('index');
    Route::post('/store', [MedicineController::class, 'storeMedicine'])->name('store');
    Route::get('/{id}/details', [MedicineController::class, 'getDetails'])->name('details');
    Route::post('/{id}/administrations', [MedicineController::class, 'storeAdministration'])->name('administrations.store');
});
