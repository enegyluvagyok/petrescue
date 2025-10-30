<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Google\Client as GoogleClient;

class SocialAuthController extends Controller
{
    public function handleGoogle(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $client = new GoogleClient(['client_id' => env('GOOGLE_CLIENT_ID_ANDROID')]); // 🔹 Android-kliens ID
        $payload = $client->verifyIdToken($request->token);

        if (!$payload) {
            return response()->json([
                'success' => false,
                'message' => 'Érvénytelen Google token.',
            ], 401);
        }

        $email = $payload['email'];
        $name  = $payload['name'] ?? explode('@', $email)[0];

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'     => $name,
                'email_verified_at' => now(),
                'password' => bcrypt(str()->random(12)),
            ]
        );

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Sikeres Google bejelentkezés',
            'data' => [
                'user'  => $user,
                'token' => $token,
            ],
        ]);
    }
}
