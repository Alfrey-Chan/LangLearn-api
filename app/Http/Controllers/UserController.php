<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function profile(Request $request)
    {
        $firebaseUid = $request->attributes->get('firebase_uid');

        $user = User::where('firebase_uid', $firebaseUid)->first();
        $stats = $user->userStats;
        
        return response()->json([
            'user' => $user,
        ]);
    }
}
