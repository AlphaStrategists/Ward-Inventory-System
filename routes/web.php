<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home'); })->name('home');

Route::get('/dashboard', function () {
    return view('dashboard'); })->name('dashboard');

Route::get('/test', function () {
    return view('test'); })->name('test');

Route::get('/login', function() {
    return view('login'); })->name('login');

