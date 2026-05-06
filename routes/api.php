<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Treasure Hunt routes
    Route::get('/hunts', [TreasureHuntController::class, 'index']);
    Route::get('/hunts/{id}', [TreasureHuntController::class, 'show']);

});
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/game/initial-scene', [GameController::class, 'initialScene']);
    Route::post('/treasures/found', [GameController::class, 'markTreasureFound']);
});
