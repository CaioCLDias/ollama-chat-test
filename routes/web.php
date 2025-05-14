<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/email-verified-success', function () {
    return view('auth.email-verified');
});
