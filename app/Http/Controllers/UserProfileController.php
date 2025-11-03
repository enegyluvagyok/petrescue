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

        // 🔹 Csak az engedélyezett mezőket vesszük
        $data = collect($request->all())->only($allowed)->toArray();

        // 🔹 Meta előkészítés
        $meta = $user->meta ?? new UserMeta(['user_id' => $user->id]);

        // 🔹 Feltöltés
        $meta->fill($data);

        // 🔹 Avatar kezelése
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $meta->avatar_path = $path;
        }

        $meta->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil sikeresen frissítve.',
            'meta' => $meta,
            'avatar_url' => $meta->avatar_path ? asset('storage/' . $meta->avatar_path) : null,
        ]);
    }
}
