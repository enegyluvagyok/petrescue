<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class SocialAuthController extends Controller
{
    // 🔹 Google login redirect (Flutternek nem kell, de teszteléshez hasznos)
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    // 🔹 Google callback (Flutter ezt hívja tokennel)
    public function handleGoogleCallback(Request $request)
    {
        try {
            // Ha Flutter küldi a token-t (preferred)
            if ($request->has('token')) {
                $googleUser = Socialite::driver('google')->stateless()->userFromToken($request->token);
            } else {
                // Ha böngészőből hívod a redirect után
                $googleUser = Socialite::driver('google')->stateless()->user();
            }

            // Megnézzük, létezik-e a user
            $user = User::where('email', $googleUser->getEmail())->first();

            // Ha nem, létrehozzuk
            if (! $user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(16)),
                ]);
            }

            // Laravel Sanctum token létrehozása
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Sikeres Google bejelentkezés.',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google hitelesítés sikertelen.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
