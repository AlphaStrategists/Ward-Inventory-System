<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Home page router
Route::get('/', function () {
    return view('home');
})->name('home');


// Auth controller routes(login)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index']);
    Route::get('/admin/users', [AdminController::class, 'users']);
});

//logout 
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

Route::get('/test', function () {
    return view('test');
})->name('test');

// User controller routers
Route::get('/users', [UserController::class, 'index'])->name('getAllUsers');
Route::get('/users/create', [UserController::class, 'create'])->name('createUser');
Route::post('/users', [UserController::class, 'store'])->name('storeUser');
Route::get('/users/{id}', [UserController::class, 'show'])->name('getUser');
