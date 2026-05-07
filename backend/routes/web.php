<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/superadmin', 'superadmin');
Route::view('/superadmin/{any}', 'superadmin')->where('any', '.*');
