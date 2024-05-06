<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    $users = User::all();
    return response()->json([
        'success' => true,
        'users' => $users,
    ]);
});

Route::prefix('/auth')->name('auth.')->group(function () {
    Route::post('/register', [RegisterController::class, 'register'])->name('register');
    Route::post('/login', [LoginController::class, 'login'])->name('login');
    Route::delete('/logout', [LoginController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
});