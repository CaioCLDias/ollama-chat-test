<?php

use App\Http\Controllers\Admin\UserControler;
use App\Http\Controllers\Common\ChatController;
use App\Http\Controllers\Common\RegisterUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('admin')->middleware(['auth:sanctum', 'admin', 'verified', 'deleted', 'throttle:6,1'])->group(function () {
    Route::get('/users', [UserControler::class, 'index'])->name('users.index');
    Route::post('/users', [UserControler::class, 'store'])->name('users.store');
    Route::get('/users/{id}', [UserControler::class, 'show'])->name('users.show');
    Route::put('/users/{id}', [UserControler::class, 'update'])->name('users.update');
    Route::post('/users/delete/{id}', [UserControler::class, 'destroy'])->name('users.destroy');
});


Route::middleware(['auth:sanctum', 'verified', 'deleted'])->group(function () {
    Route::post('/user', [RegisterUserController::class, 'destroy']);
    Route::post('/chat', [ChatController::class, 'store'])->name('chat.store');
});

require __DIR__ . '/auth.php';
