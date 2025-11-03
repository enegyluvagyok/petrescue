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

        // 🔹 camelCase → snake_case konverzió
        $snakeCased = [];
        foreach ($request->all() as $key => $value) {
            $snakeKey = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $key));
            $snakeCased[$snakeKey] = $value;
        }
        $request->merge($snakeCased);

        // 🔹 Meta rekord lekérése vagy létrehozása
        $meta = $user->meta ?: new UserMeta();
        $meta->user_id = $user->id;

        // 🔹 Mezők kitöltése
        $meta->fill($request->only([
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
        ]));

        // 🔹 Profilkép feltöltése (PUT-nál is működik)
        if ($request->hasFile('avatar')) {
            if ($meta->avatar_path && Storage::disk('public')->exists($meta->avatar_path)) {
                Storage::disk('public')->delete($meta->avatar_path);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $meta->avatar_path = $path;
        }

        $meta->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil sikeresen frissítve.',
            'meta' => $meta->fresh(),
            'avatar_url' => $meta->avatar_path
                ? asset('storage/' . $meta->avatar_path)
                : null,
        ]);
    }
}
