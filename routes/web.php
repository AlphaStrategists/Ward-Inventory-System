<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/test-geninv', function () {
    return view('Pages.GenInventory');
});
