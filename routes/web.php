<?php

use App\Http\Controllers\Admin\AdminWebController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebController::class, 'login']);
Route::get('/play', [WebController::class, 'play']);
Route::get('/leaderboard', [WebController::class, 'leaderboard']);
Route::get('/profile', [WebController::class, 'profile']);

Route::get('/admin', [AdminWebController::class, 'dashboard']);

Route::get('/db-users', function () {
    $users = App\Models\User::all(['id', 'name', 'email', 'role', 'current_level', 'locale']);
    return view('db-users', compact('users'));
});
