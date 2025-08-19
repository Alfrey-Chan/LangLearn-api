<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Kreait\Firebase\Auth;
use Exception;

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

            return $next($request);

        } catch (Exception $e) {
            return response()->json(['error' => 'Invalid or expired token'], 401);
        }
    }
}
