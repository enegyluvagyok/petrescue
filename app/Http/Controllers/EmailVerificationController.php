<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class EmailVerificationController extends Controller
{

    public function verifyFromApp(Request $request)
    {
        try {
            $token = $request->input('token');
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hiányzó token.',
                ], 400);
            }

            // 🔹 Token dekódolása
            $decoded = base64_decode(strtr($token, '-_', '+/'));
            if (!$decoded) {
                return response()->json([
                    'success' => false,
                    'message' => 'Érvénytelen token.',
                ], 400);
            }

            // 🔹 Az eredeti Laravel verify URL visszafejtése
            $verifyUrl = $decoded;

            // 🔹 Paraméterek kinyerése (userId + hash)
            preg_match('~/email/verify/(\d+)/([^?]+)~', $verifyUrl, $matches);
            if (count($matches) < 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hibás verifikációs link.',
                ], 400);
            }

            [$full, $userId, $hash] = $matches;
            $user = User::find($userId);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Felhasználó nem található.',
                ], 404);
            }

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Az e-mail már meg volt erősítve.',
                ]);
            }

            // 🔹 Ellenőrzés, hogy a hash megegyezik-e
            if (!hash_equals(sha1($user->getEmailForVerification()), $hash)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Érvénytelen megerősítő link.',
                ], 400);
            }

            // 🔹 Email megjelölése verified-ként
            $user->markEmailAsVerified();
            event(new Verified($user));

            return response()->json([
                'success' => true,
                'message' => 'Email sikeresen megerősítve.',
                'data' => [
                    'user' => $user,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Email verify-app hiba: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Szerverhiba történt.',
            ], 500);
        }
    }
}
