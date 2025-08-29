Here’s a clean, revised firebase_authentication_flow.md you can drop into your repo. It keeps things simple, explains why each step exists, and uses code you can copy-paste.

⸻

Firebase Authentication Flow (Laravel 12 + Firebase Auth)

This guide explains what happens when your Web frontend signs a user in with Firebase and your Laravel 12 API verifies that sign-in securely.

⸻

Overview
	1.	The frontend signs in with Firebase (Google, Email/Password, etc.) and receives an ID token.
	2.	The frontend sends that ID token to your Laravel API in the Authorization header.
	3.	Laravel verifies the token with the Firebase Admin SDK (Kreait), reads the user info (claims), and (optionally) creates/updates a user row in your DB.

⸻

Prerequisites
	•	You have a Firebase service account JSON (downloaded from Firebase Console → Project settings → Service accounts → Generate new private key).
	•	You’ve installed Kreait’s Laravel integration:

composer require kreait/laravel-firebase


	•	You placed the JSON in a non-public folder (e.g., storage/keys/firebase.json) and restricted permissions:

chmod 600 storage/keys/firebase.json


	•	Your .env has:

FIREBASE_CREDENTIALS=storage/keys/firebase.json
FIREBASE_PROJECT_ID=your-project-id



After changing .env or config, run:

php artisan config:clear && php artisan optimize:clear



⸻

Service Binding (makes Firebase Auth available via DI)

File: app/Providers/AppServiceProvider.php

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth as FirebaseAuth;

public function register(): void
{
    $this->app->singleton(FirebaseAuth::class, function () {
        return (new Factory)
            ->withServiceAccount(config('firebase.credentials')) // path/array from config
            ->withProjectId(env('FIREBASE_PROJECT_ID'))          // avoid “Unable to determine Project ID”
            ->createAuth();
    });
}

If you didn’t publish config/firebase.php, you can also point directly to the JSON path with ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS'))).

⸻

Route (rate-limited)

File: routes/api.php

use App\Http\Controllers\AuthController;

Route::post('/auth/firebase-login', [AuthController::class, 'firebaseLogin'])
    ->middleware('throttle:auth'); // rate limit defined below

File: bootstrap/app.php (define a simple rate limiter)

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('auth', function (Request $request) {
    return Limit::perMinute(10)->by($request->ip());
});


⸻

Controller (verify token, upsert user, return JSON)

File: app/Http/Controllers/AuthController.php

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

class AuthController extends Controller
{
    public function __construct(private FirebaseAuth $firebaseAuth) {}

    public function firebaseLogin(Request $request)
    {
        // Prefer the Authorization header (Bearer <token>)
        $token = $request->bearerToken();

        // Optional: allow JSON body fallback if you really want
        if (!$token) {
            $request->validate(['firebase_token' => 'required|string']);
            $token = (string) $request->input('firebase_token');
        }

        try {
            // Verify the Firebase ID token (pass true to also check revocation)
            $verified = $this->firebaseAuth->verifyIdToken($token /*, true*/);

            // Extract claims (may be null depending on provider)
            $claims = $verified->claims();
            $uid   = $claims->get('sub');         // Firebase UID (required)
            $email = $claims->get('email');       // may be null
            $name  = $claims->get('name');        // may be null

            // Find or create/update a local user
            $user = User::updateOrCreate(
                ['firebase_uid' => $uid],
                ['email' => $email ?: '', 'name' => $name ?: '']
            );

            return response()->json([
                'message'      => 'Login successful',
                'firebase_uid' => $uid,
                'user'         => $user
            ], 200);

        } catch (FailedToVerifyToken $e) {
            return response()->json(['error' => 'Invalid or expired token'], 401);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Auth error'], 401);
        }
    }
}


⸻

Database (recommended unique index)

Add (one-time) migration fields for mapping Firebase users:

$table->string('firebase_uid')->unique();
$table->string('email')->nullable();
$table->string('name')->nullable();

This lets you identify the same Firebase user across requests.

⸻

Frontend → API request

Always send the ID token in the Authorization header (don’t send it twice):

POST /api/auth/firebase-login
Authorization: Bearer <firebase-id-token>
Content-Type: application/json

{}


⸻

What “verification” actually does

When you call verifyIdToken($token):
	•	The Admin SDK validates the JWT locally using Google public keys (it fetches and caches these keys as needed).
	•	It checks the signature, expiry, issuer/audience (your project), and returns a VerifiedIdToken if valid.
	•	If you pass true as the 2nd argument, it also checks for revocation (can involve a remote lookup).

No passwords are handled by your API; Firebase took care of sign-in on the client.

⸻

Security Essentials (plain English)
	•	Use HTTPS
Without HTTPS, someone on the same network can intercept the token. Use TLS in production.
	•	Don’t log the raw token
Treat it like a password. If you must log, redact it:

\Log::info('Login request received', ['hasToken' => (bool) $request->bearerToken()]);


	•	Rate-limit your login route
Stops abuse (bots hammering your endpoint). We applied throttle:auth (10/min per IP) above.
	•	Keep the service account JSON private
Store it outside public/ (e.g., storage/keys/firebase.json) and restrict permissions:

chmod 600 storage/keys/firebase.json

Don’t commit it to git.

	•	Clear config cache after changes
When you update .env or config/*, run:

php artisan config:clear && php artisan optimize:clear

Otherwise Laravel might keep using old values.

⸻

Troubleshooting
	•	“Unable to determine the Firebase Project ID”
Add FIREBASE_PROJECT_ID=your-project-id to .env and include ->withProjectId(env('FIREBASE_PROJECT_ID')) in your service binding.
	•	“Target class [firebase.auth] does not exist.”
Ensure kreait/laravel-firebase is installed, auto-discovery is not blocked in composer.json, and you imported the correct classes. Use DI: public function __construct(FirebaseAuth $firebaseAuth).
	•	401 after ~1 hour
ID tokens expire. The frontend Firebase SDK refreshes tokens automatically; make sure your client re-sends the fresh token on each request.
	•	Web only (no Android/iOS yet)
You do not need SHA-1/256 fingerprints. Those are only for Android builds.

⸻

Minimal health check

Add this temporary route to ensure the binding resolves and credentials load:

use Kreait\Firebase\Auth;

Route::get('/firebase-check', function (Auth $auth) {
    return ['ok' => true];
});

If it returns {"ok": true}, your server is wired correctly.

⸻

That’s it! This flow keeps your API stateless and secure: the client proves identity with a Firebase ID token; your server verifies it and uses that identity in your app.