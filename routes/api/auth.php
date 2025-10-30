<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\SocialAuthController;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| AUTH MODULE ROUTES
|--------------------------------------------------------------------------
| Ez a fájl az összes authentikációs, email-verifikációs,
| jelszó-visszaállítási és third-party (Google) route-ot tartalmazza.
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {

    // ─────────────────────────────
    // 🧩 TESZT ENDPOINT
    // ─────────────────────────────
    Route::get('/test', fn() => response()->json(['status' => 'Auth API működik!']));

    // ─────────────────────────────
    // 🔐 REGISTER / LOGIN / LOGOUT / ME
    // ─────────────────────────────
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    // ─────────────────────────────
    // 📬 EMAIL VERIFICATION
    // ─────────────────────────────
    Route::get('/email/verify/{id}/{hash}', function ($id, $hash, Request $request) {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'success' => false,
                'message' => 'Érvénytelen vagy lejárt verifikációs link.',
            ], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Az email címed már meg volt erősítve.',
            ]);
        }

        $user->markEmailAsVerified();

        return response()->json([
            'success' => true,
            'message' => 'Email sikeresen megerősítve!',
        ]);
    })->name('verification.verify');

    Route::middleware('auth:sanctum')->post('/email/verify/resend', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Az email címed már meg van erősítve.']);
        }

        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Megerősítő e-mail újraküldve.']);
    });

    // ─────────────────────────────
    // 🔑 PASSWORD RESET
    // ─────────────────────────────
    Route::prefix('password')->group(function () {
        Route::post('/forgot', [PasswordResetController::class, 'forgot']);
        Route::post('/reset', [PasswordResetController::class, 'reset']);
        Route::get('/reset/confirm', function (Request $request) {
            return response()->json([
                'success' => true,
                'message' => 'A jelszó visszaállítási link érvényes.',
                'email' => $request->query('email'),
                'token' => $request->query('token'),
            ]);
        });
    });

    // ─────────────────────────────
    // 🌐 THIRD-PARTY LOGIN (GOOGLE)
    // ─────────────────────────────
        Route::post('/google', [SocialAuthController::class, 'handleGoogle']);
});
