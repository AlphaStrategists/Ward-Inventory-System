<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function() {
    return view('components/login');
});

Route::post('/login', [UserController::class, 'login'])-> name('login');
Route::get('/password/reset', [UserController::class, 'showPasswordResetForm'])-> name('password.request');
