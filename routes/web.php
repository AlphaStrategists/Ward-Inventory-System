<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NarcoticController;
use App\Http\Controllers\SyrupController;
use App\Http\Controllers\IvFluidController;
use App\Http\Controllers\OralCountableController;
use App\Http\Controllers\AntibioticController;
use App\Http\Controllers\OralAntibioticController;

Route::get('/', [NarcoticController::class, 'index']);
Route::get('/narcotics', [NarcoticController::class, 'index'])->name('narcotics.index');
Route::get('/syrup', [SyrupController::class, 'index'])->name('syrup.index');
Route::get('/iv-fluid', [IvFluidController::class, 'index'])->name('iv-fluid.index');
Route::get('/oral-countable', [OralCountableController::class, 'index'])->name('oral-countable.index');
Route::get('/antibiotic', [AntibioticController::class, 'index'])->name('antibiotic.index');
Route::get('/oral-antibiotic', [OralAntibioticController::class, 'index'])->name('oral-antibiotic.index');

