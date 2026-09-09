<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [UserController::class, 'login'])-> name('login');
Route::get('/home', [UserController::class, 'home'])->name('home');
Route::get('/password/reset', [UserController::class, 'showPasswordResetForm'])->name('password.request');
