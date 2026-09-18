<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

// Home page routes
Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/login', function() {
    return view('login');
})->name('login');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index']);
    Route::get('/admin/users', [AdminController::class, 'users']);
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/test', function () {
    return view('test');
})->name('test');

// User controller routes
Route::get('/users', [Controller::class, 'index'])->name('getAllUsers');
Route::get('/users/create', [Controller::class, 'create'])->name('createUser');
Route::post('/users', [Controller::class, 'store'])->name('storeUser');
Route::get('/users/{id}', [Controller::class, 'show'])->name('getUser');
