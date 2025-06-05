<?php

use App\Http\Controllers\Common\ChatController;
use App\Http\Controllers\Common\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('user')->group(function () {

    Route::post('/', [UserController::class, 'store'])
        ->middleware('guest')
        ->name('user.store');

    Route::get('/', [UserController::class, 'show'])
        ->middleware(['auth:sanctum', 'verified', 'deleted'])
        ->name('user.show');

    Route::put('/', [UserController::class, 'update'])
        ->middleware(['auth:sanctum', 'verified', 'deleted'])
        ->name('user.update');

    Route::delete('/delete', [UserController::class, 'destroy'])
        ->middleware(['auth:sanctum', 'verified', 'deleted'])
        ->name('user.delete');

    Route::post('/cancel-deletion', [UserController::class, 'cancelDeletion'])
        ->middleware(['auth:sanctum', 'verified', 'deleted'])
        ->name('user.cancel-deletion');
});

Route::middleware(['auth:sanctum', 'verified', 'deleted'])->group(function () {
    Route::post('/chat', [ChatController::class, 'store'])->name('chat.store');
    Route::get('/chat/history', [ChatController::class, 'getChatHistory'])->name('chat.getChatHistory');
});

require __DIR__ . '/auth.php';
