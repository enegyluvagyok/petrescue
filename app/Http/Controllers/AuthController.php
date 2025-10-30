<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Throwable;

class AuthController extends Controller
{
    // 🔹 Regisztráció
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // 🔹 Küldjük el az email verification linket
            $user->sendEmailVerificationNotification();

            return response()->json([
                'success' => true,
                'message' => 'Sikeres regisztráció! Kérlek, erősítsd meg az e-mailedet a küldött linken.',
                'data' => [
                    'user' => $user,
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hibásan megadott adatok.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Szerverhiba történt a regisztráció során.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // 🔹 Bejelentkezés
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $validated['email'])->first();

            // 🔹 Hibás email vagy jelszó
            if (! $user || ! Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hibás email vagy jelszó.',
                    'errors' => ['email' => ['Hibás email vagy jelszó.']],
                ], 401);
            }

            // 🔹 Ellenőrizzük, hogy az e-mail megerősítve van-e
            if (! $user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kérlek, erősítsd meg az e-mail címedet, mielőtt bejelentkezel.',
                    'errors' => ['email' => ['A fiók még nincs megerősítve.']],
                ], 403);
            }

            // 🔹 (Opcionális) régi tokenek törlése, hogy mindig csak 1 aktív legyen
            $user->tokens()->delete();

            // 🔹 Új token generálása
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Sikeres bejelentkezés.',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hibásan megadott adatok.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Szerverhiba történt a bejelentkezés során.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // 🔹 Saját profil lekérése
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }

    // 🔹 Kijelentkezés
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sikeresen kijelentkeztél.',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Szerverhiba történt kijelentkezés közben.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
