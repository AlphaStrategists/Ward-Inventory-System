<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/inj', function () {
    $drug = (object) [
        'name' => 'Diazepam',
        'stock_balance' => 85,
        'unit' => 'tabs',
    ];

    return view('pages.Injection', ['drug' => $drug]);
});
Route::get('/injA', function () {
  
       

    return view('pages.InjectionAntibiotic');
});