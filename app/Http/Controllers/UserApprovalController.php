<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserApprovalController extends Controller
{
    public function pending()
    {
        $users = User::where('is_approved', false)
            ->whereNotNull('email_verified_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    public function approve($id)
    {
        $user = User::findOrFail($id);
        $user->is_approved = true;
        $user->save();

        // Itt opcionálisan értesítést küldhetünk e-mailben
        // $user->notify(new AccountApprovedNotification());

        return response()->json([
            'success' => true,
            'message' => "{$user->name} jóváhagyva.",
        ]);
    }

    public function reject($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Felhasználó elutasítva és törölve.',
        ]);
    }
}
