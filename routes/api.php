<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\SystemFlagController;

// Minden auth-modul betöltése
require __DIR__ . '/api/auth.php';

// Ide jöhetnek más modulok (pl. devices, admin, tests, stb.)

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/profile', [UserProfileController::class, 'show']);
    Route::put('/user/profile', [UserProfileController::class, 'update']);
});


// routes/api.php
Route::middleware('auth:sanctum')->get('/users', function () {
    return response()->json([
        'success' => true,
        'data' => \App\Models\User::with('meta')
            ->select('id', 'name', 'email')
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'meta' => $u->meta,
                'avatar_url' => $u->meta?->avatar_path
                    ? asset('storage/' . $u->meta->avatar_path)
                    : null,
            ]),
    ]);
});


// restart érték lekérdezés
Route::get('/restart', [SystemFlagController::class, 'getRestart']);

// restart mód beállítása (0 vagy 1)
Route::post('/restart/{value}', [SystemFlagController::class, 'setRestart'])
    ->where('value', '[01]');