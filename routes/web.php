<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MedicineController;

Route::get('/test', function () {
    return "Laravel is working perfectly!";
});

Route::get('/', function () {
    return redirect()->route('medicines.index');
});

Route::prefix('medicines')->name('medicines.')->group(function () {
    Route::get('/', [MedicineController::class, 'index'])->name('index');
    Route::get('/{id}/details', [MedicineController::class, 'getDetails'])->name('details');
});
