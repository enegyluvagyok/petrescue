<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Providers\AuthServiceProvider;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    // 🔹 Elfelejtett jelszó - email küldés
    public function forgot(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Ezzel az e-mail címmel nem található felhasználó.'
            ], 404);
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now(),
            ]
        );

        $resetUrl = url("/api/auth/password/reset/confirm?email={$user->email}&token={$token}");
        $shortUrl = AuthServiceProvider::shortenUrl($resetUrl, 'reset');

        Mail::send('emails.reset', ['user' => $user, 'resetUrl' => $shortUrl], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('🔑 Jelszó visszaállítása');
        });

        return response()->json([
            'success' => true,
            'message' => 'A jelszó-visszaállító link elküldve az e-mail címedre.'
        ]);
    }

    // 🔹 Új jelszó beállítása
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (! $resetRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Nem található visszaállítási token.'
            ], 404);
        }

        // Ellenőrizzük a tokent
        if (! Hash::check($request->token, $resetRecord->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Érvénytelen token.'
            ], 403);
        }

        // Token lejárati idő (1 óra)
        if (Carbon::parse($resetRecord->created_at)->addHour()->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'A token lejárt, kérj újat.'
            ], 410);
        }

        // Jelszó frissítése
        $user = User::where('email', $request->email)->firstOrFail();
        $user->update(['password' => Hash::make($request->password)]);

        // Token törlése
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'A jelszavad sikeresen frissült.'
        ]);
    }
}
