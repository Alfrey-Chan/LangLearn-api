<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\UserStatistic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Auth as FirebaseAuth;

class AuthController extends Controller
{
    protected FirebaseAuth $firebaseAuth;

    public function __construct(FirebaseAuth $firebaseAuth)
    {
        $this->firebaseAuth = $firebaseAuth;
    }

    public function firebaseLogin(Request $request)
    {   
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Authorization token required'], 401);
        }

        try {
            $verifiedIdToken = $this->firebaseAuth->verifyIdToken($token);
            
            // Get user data from Firebase token
            $uid = $verifiedIdToken->claims()->get('sub');
            $email = $verifiedIdToken->claims()->get('email');

            // Find or create user in our database
            $user = User::updateOrCreate(
                ['firebase_uid' => $uid],
                ['email' => $email],
            );

            $userStats = UserStatistic::where('firebase_uid', $uid)->first();
            
            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'user_stats' => $userStats,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Invalid Firebase token',
                'message' => $e->getMessage()
            ], 401);
        }
    }
}
