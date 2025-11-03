<?php

namespace App\Http\Controllers;

use App\Models\UserMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user()?->load('meta');

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'meta' => $user->meta,
                'avatar_url' => $user->meta?->avatar_path
                    ? asset('storage/' . $user->meta->avatar_path)
                    : null,
            ],
        ]);
    }

 public function update(Request $request)
{
    $user = Auth::user();
    if (!$user) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    $allowed = [
        'birth_place',
        'mother_name',
        'birth_date',
        'postal_code',
        'city',
        'street_name',
        'street_type',
        'house_number',
        'floor',
        'door',
        'id_card_number',
        'taj_number',
        'tax_id',
    ];

    $data = collect($request->all())->only($allowed)->toArray();

    $meta = $user->meta ?? new UserMeta(['user_id' => $user->id]);
    $meta->fill($data);

    // 🔹 Avatar kezelése biztonságosan
    if ($request->hasFile('avatar')) {
        try {
            $file = $request->file('avatar');

            if (!$file->isValid()) {
                throw new \Exception('A feltöltött fájl érvénytelen.');
            }

            $path = $file->store('avatars', 'public');
            if (!$path) {
                throw new \Exception('Nem sikerült menteni az avatart.');
            }

            $meta->avatar_path = $path;
        } catch (\Throwable $e) {
            \Log::error('Avatar feltöltési hiba: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Avatar mentési hiba: ' . $e->getMessage(),
            ], 500);
        }
    }

    try {
        $meta->save();
    } catch (\Throwable $e) {
        \Log::error('Meta mentési hiba: ' . $e->getMessage(), [
            'user_id' => $user->id,
            'data' => $data,
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Meta mentése sikertelen: ' . $e->getMessage(),
        ], 500);
    }

    return response()->json([
        'success' => true,
        'message' => 'Profil sikeresen frissítve.',
        'meta' => $meta,
        'avatar_url' => $meta->avatar_path ? asset('storage/' . $meta->avatar_path) : null,
    ]);
}

}
