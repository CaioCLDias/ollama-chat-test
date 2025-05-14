<?php

use App\Http\Controllers\Admin\UserControler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::apiResource('users', UserControler::class);
});


require __DIR__ . '/auth.php';
