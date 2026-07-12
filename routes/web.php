<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function() {
    return view('components/login');
});

Route::get('/home', function() {
    return view('components/home');
});
