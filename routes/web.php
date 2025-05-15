<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Redirect::to('/up');
});

Route::get('/email-verified-success', function () {
    return view('auth.email-verified');
});
