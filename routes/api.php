<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserProfileController;

// Minden auth-modul betöltése
require __DIR__ . '/api/auth.php';

// Ide jöhetnek más modulok (pl. devices, admin, tests, stb.)

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/profile', [UserProfileController::class, 'show']);
    Route::put('/user/profile', [UserProfileController::class, 'update']);
});
