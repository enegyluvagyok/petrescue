<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Device;
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
                'device_uuid' => 'required|string',
                'platform' => 'nullable|string',
                'device_name' => 'nullable|string',
                'push_token' => 'nullable|string',
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (! $user || ! Hash::check($validated['password'], $user->password)) {
                return response()->json(['success' => false, 'message' => 'Hibás email vagy jelszó.'], 401);
            }

            if (! $user->hasVerifiedEmail()) {
                return response()->json(['success' => false, 'message' => 'Erősítsd meg az e-mailedet előbb.'], 403);
            }

            // 🔹 Tokent NEM töröljük mostantól
            $token = $user->createToken('mobile')->plainTextToken;

            // 🔹 Eszköz frissítés vagy létrehozás
            $device = Device::updateOrCreate(
                ['user_id' => $user->id, 'device_uuid' => $validated['device_uuid']],
                [
                    'name' => $validated['device_name'] ?? 'Ismeretlen eszköz',
                    'platform' => $validated['platform'] ?? 'unknown',
                    'push_token' => $validated['push_token'] ?? null,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'revoked' => false,
                    'last_active_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Sikeres bejelentkezés.',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'device' => $device,
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Szerverhiba', 'error' => $e->getMessage()], 500);
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

    public function listDevices(Request $request)
    {
        $devices = $request->user()->devices()->get();
        return response()->json(['success' => true, 'data' => $devices]);
    }

    public function revokeDevice(Request $request)
    {
        $request->validate(['device_uuid' => 'required|string']);
        $updated = Device::where('user_id', $request->user()->id)
            ->where('device_uuid', $request->device_uuid)
            ->update(['revoked' => true]);
        return response()->json(['success' => (bool) $updated]);
    }

    // 🔹 Kijelentkezés
    public function logout(Request $request)
    {
        try {
            $deviceUuid = $request->input('device_uuid');

            // Token törlése
            $request->user()->currentAccessToken()->delete();

            // Eszköz "revoked" jelölés
            if ($deviceUuid) {
                Device::where('user_id', $request->user()->id)
                    ->where('device_uuid', $deviceUuid)
                    ->update(['revoked' => true, 'last_active_at' => now()]);
            }

            return response()->json(['success' => true, 'message' => 'Sikeresen kijelentkezve.']);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Szerverhiba kijelentkezés közben.'], 500);
        }
    }
}
