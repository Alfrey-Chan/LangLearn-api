<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Models\User;
use Kreait\Firebase\Auth;
use Illuminate\Http\Request;
use App\Models\UserStatistic;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class FirebaseAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the Bearer token
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Authorization token required'], 401);
        }

        try {
            // Get Firebase Auth instance from service container
            $firebaseAuth = app(Auth::class);
            
            // Verify the Firebase ID token
            $verifiedIdToken = $firebaseAuth->verifyIdToken($token);
            
            // Get user information from the token
            $uid = $verifiedIdToken->claims()->get('sub');
            $email = $verifiedIdToken->claims()->get('email');
            
            // Add user info to the request for use in controllers
            $request->attributes->set('firebase_uid', $uid);
            $request->attributes->set('firebase_email', $email);
            $request->attributes->set('firebase_user', $verifiedIdToken->claims()->all());

            // Create user if doesn't exist
            User::updateOrCreate(
                ['firebase_uid' => $uid],
                ['email' => $email]
            );
            
            // Update streak on any activity
            $this->updateUserStreak($uid);
           

            return $next($request);

        } catch (Exception $e) {
            Log::error('Firebase Auth Error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid or expired token', 'debug' => $e->getMessage()], 401);
        }
    }

    private function updateUserStreak($uid)
    {
        $stats = UserStatistic::updateOrCreate(['firebase_uid' => $uid], ['firebase_uid' => $uid]);
        $today = now()->toDateString();
        
        // Only run once per day per user
        if ($stats->last_activity_date === $today) {
            return; // Already processed today
        }
        
        $lastActivity = $stats->last_activity_date;
        
        if (!$lastActivity) {
            // First time user
            $stats->update([
                'last_activity_date' => $today,
                'current_streak' => 1,
                'longest_streak' => 1
            ]);
        } elseif ($lastActivity === now()->subDay()->toDateString()) {
            // Consecutive day - increment streak
            $newStreak = $stats->current_streak + 1;
            $stats->update([
                'last_activity_date' => $today,
                'current_streak' => $newStreak,
                'longest_streak' => max($stats->longest_streak, $newStreak)
            ]);
        } else {
            // Missed a day - reset streak
            $stats->update([
                'last_activity_date' => $today,
                'current_streak' => 1,
                'longest_streak' => max($stats->longest_streak, 1)
            ]);
        }
    }
}
