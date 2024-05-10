<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Gpt\BiologyController;
use App\Http\Controllers\Gpt\GeographyController;
use App\Http\Controllers\Gpt\HistoryController;
use App\Http\Controllers\Gpt\ParaphraseController;
use App\Http\Controllers\UserController;
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

Route::prefix('/user')->controller(UserController::class)->middleware('auth:sanctum')->name('user.')->group(function () {
    Route::get('/', [UserController::class, 'getUserByToken'])->name('getByToken');
    Route::post('/premium-purchase', 'premiumPurchase')->name('premiumPurchase');
});

Route::prefix('/topic-paraphrase')->controller(ParaphraseController::class)->middleware('auth:sanctum')->name('paraphrase.')->group(function () {
    Route::post('/chat', 'defaultTopicParaphrase')->name('chat');
    Route::post('/test/{paraphraseId}', 'paraphraseTest')->middleware('checkPremium')->name('test');
});
