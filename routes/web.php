<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home'); })->name('home');

Route::get('/about', function () {
    return view('about'); })->name('about');

Route::get('/contact', function () {
    return view('dashboard'); })->name('dashboard');

    Route::get('/test', function() {
        return view('test'); })->name('test');