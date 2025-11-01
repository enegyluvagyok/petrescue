<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\Admin\UserApprovalController;
use App\Http\Controllers\EmailVerificationController;
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

        Route::post('/forgot',function (Request $request) {
            $request->validate(['email' => 'required|email']);

            $status = Password::sendResetLink($request->only('email'));

            return response()->json([
                'success' => $status === Password::RESET_LINK_SENT,
                'message' => __($status),
            ]);
        });

        Route::post('/reset', function (Request $request) {
                $request->validate([
                    'email' => 'required|email',
                    'token' => 'required',
                    'password' => 'required|min:8|confirmed',
                ]);

                $status = Password::reset(
                    $request->only('email', 'password', 'password_confirmation', 'token'),
                    function ($user, $password) {
                        $user->forceFill(['password' => Hash::make($password)])->save();
                    }
                );

                return response()->json([
                    'success' => $status === Password::PASSWORD_RESET,
                    'message' => __($status),
                ]);
            });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/devices', [AuthController::class, 'listDevices']);
        Route::post('/devices/revoke', [AuthController::class, 'revokeDevice']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/users/pending', [UserApprovalController::class, 'pending']);
        Route::post('/users/{id}/approve', [UserApprovalController::class, 'approve']);
        Route::post('/users/{id}/reject', [UserApprovalController::class, 'reject']);
    });

    // ─────────────────────────────
    // 🌐 E-MAIL VERIFICATION FROM APP
    // ─────────────────────────────
    Route::post('/email/verify-app', function (Request $request) {
        $token = $request->input('token');
        $user = User::whereRaw("SHA1(email) = ?", [$token])->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Hibás verifikációs link.',
            ], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'E-mail már korábban megerősítve.',
            ]);
        }

        $user->markEmailAsVerified();

        return response()->json([
            'success' => true,
            'message' => 'E-mail sikeresen megerősítve!',
        ]);
    });
});
