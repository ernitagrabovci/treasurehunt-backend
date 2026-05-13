<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

// Auth routes (no auth required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail']);

// Authenticated user routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Game routes (authenticated)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/game/scenes', [GameController::class, 'scenes']);
    Route::get('/game/initial-scene', [GameController::class, 'initialScene']);
    Route::get('/game/scene/{id}', [GameController::class, 'scene']);
    Route::post('/game/navigate', [GameController::class, 'navigate']);
    Route::post('/game/advance-level', [GameController::class, 'advanceLevel']);
    Route::get('/treasures', [GameController::class, 'treasures']);
    Route::get('/treasures/check/{hotspotId}', [GameController::class, 'checkTreasure']);
    Route::post('/treasures/found', [GameController::class, 'markTreasureFound']);
    Route::get('/leaderboard', [GameController::class, 'leaderboard']);
    Route::get('/user/level', [GameController::class, 'myLevel']);
    Route::get('/game/level-progress', [GameController::class, 'levelProgress']);
});

// Scene images (public - served through backend for management)
Route::get('/scene-image/{id}', [GameController::class, 'sceneImage']);
Route::get('/scene-image-by-path/{filename}', function ($filename) {
    $path = public_path($filename);
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path);
});

// Admin routes (authenticated + admin role)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/scenes', [AdminController::class, 'indexScenes']);
    Route::get('/scenes/{id}', [AdminController::class, 'showScene']);
    Route::post('/scenes', [AdminController::class, 'storeScene']);
    Route::put('/scenes/{id}', [AdminController::class, 'updateScene']);
    Route::delete('/scenes/{id}', [AdminController::class, 'deleteScene']);
    Route::post('/upload-image', [AdminController::class, 'uploadImage']);
    Route::get('/hotspots', [AdminController::class, 'indexHotspots']);
    Route::get('/hotspots/{id}', [AdminController::class, 'showHotspot']);
    Route::post('/hotspots', [AdminController::class, 'storeHotspot']);
    Route::put('/hotspots/{id}', [AdminController::class, 'updateHotspot']);
    Route::delete('/hotspots/{id}', [AdminController::class, 'deleteHotspot']);
});
