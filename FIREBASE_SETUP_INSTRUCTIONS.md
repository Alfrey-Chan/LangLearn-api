# Firebase Authentication Setup Instructions for Laravel

## Overview
This guide walks you through setting up Firebase Authentication in your Laravel API application. Firebase handles user authentication on the client-side, while your Laravel API verifies Firebase tokens and manages user data.

---

## Step 1: Install Firebase Admin SDK

```bash
composer require kreait/firebase-php
```

**What this does:** Installs the Firebase Admin SDK for PHP, which allows your Laravel server to communicate with Firebase services and verify ID tokens.

---

## Step 2: Get Firebase Service Account Credentials

### 2.1 In Firebase Console:
1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project
3. Go to **Project Settings** (gear icon)
4. Click **Service Accounts** tab
5. Click **Generate new private key**
6. Download the JSON file

### 2.2 Store Credentials:
```bash
# Create storage directory for Firebase
mkdir -p storage/firebase

# Move downloaded file (rename as needed)
mv ~/Downloads/your-project-firebase-adminsdk-xxxxx.json storage/firebase/firebase_credentials.json
```

**What this does:** The service account JSON contains private keys that allow your Laravel app to authenticate with Firebase as an admin, enabling it to verify user tokens.

---

## Step 3: Configure Environment Variables

### 3.1 Update `.env` file:
```env
# Firebase Configuration
FIREBASE_CREDENTIALS=/path/to/your/project/storage/firebase/firebase_credentials.json
```

**What this does:** Tells Laravel where to find your Firebase credentials. Keep this path secure and never commit credentials to version control.

---

## Step 4: Create Firebase Configuration File

### 4.1 Create `config/firebase.php`:
```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Firebase Admin SDK
    |
    */    
    'credentials' => env('FIREBASE_CREDENTIALS'),
];
```

**What this does:** Creates a Laravel configuration file that reads your Firebase credentials from environment variables. This follows Laravel's configuration pattern and keeps credentials secure.

---

## Step 5: Register Firebase in Service Container

### 5.1 Update `app/Providers/AppServiceProvider.php`:
```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Auth::class, function() {
            return (new Factory)
                ->withServiceAccount(config('firebase.credentials'))
                ->createAuth();
        });
    }

    public function boot(): void
    {
        //
    }
}
```

**What this does:** 
- Registers Firebase Auth as a singleton service in Laravel's container
- Uses the Factory pattern to create a Firebase Auth instance using your credentials
- Makes Firebase Auth available for dependency injection throughout your app

---

## Step 6: Create Firebase Authentication Middleware

### 6.1 Generate middleware:
```bash
php artisan make:middleware FirebaseAuth
```

### 6.2 Update `app/Http/Middleware/FirebaseAuth.php`:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Kreait\Firebase\Auth;
use Exception;

class FirebaseAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // Get the Authorization header
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Authorization token required'], 401);
        }

        // Extract the token (remove "Bearer " prefix)
        $token = substr($authHeader, 7);

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
```

**What this does:**
- Intercepts incoming requests to protected routes
- Extracts the Bearer token from Authorization header
- Verifies the token with Firebase
- Adds user information to the request for controllers to use
- Returns 401 error for invalid/missing tokens

---

## Step 7: Register Middleware

### 7.1 Update `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'firebase.auth' => \App\Http\Middleware\FirebaseAuth::class,
    ]);
})
```

**What this does:** Registers your middleware with Laravel so you can use it on routes with `->middleware('firebase.auth')`.

---

## Step 8: Update User Model for Firebase

### 8.1 Create migration for firebase_uid:
```bash
php artisan make:migration add_firebase_uid_to_users_table
```

### 8.2 Update migration file:
```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('firebase_uid')->unique()->nullable();
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('firebase_uid');
    });
}
```

### 8.3 Run migration:
```bash
php artisan migrate
```

### 8.4 Update `app/Models/User.php`:
```php
protected $fillable = [
    'firebase_uid',
    'name',
    'email',
];

protected $hidden = [
    'remember_token',
];

protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
    ];
}

/**
 * Find or create a user by Firebase UID
 */
public static function findOrCreateByFirebaseUid(string $uid, array $userData = []): User
{
    $user = static::where('firebase_uid', $uid)->first();
    
    if (!$user) {
        $user = static::create([
            'firebase_uid' => $uid,
            'name' => $userData['name'] ?? '',
            'email' => $userData['email'] ?? '',
        ]);
    }
    
    return $user;
}
```

**What this does:**
- Adds firebase_uid column to store Firebase user IDs
- Removes password-related fields (Firebase handles authentication)
- Adds helper method to find/create users by Firebase UID
- Connects Firebase users to your local user records

---

## Step 9: Create Authentication Controller

### 9.1 Create controller:
```bash
php artisan make:controller AuthController
```

### 9.2 Update `app/Http/Controllers/AuthController.php`:
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Auth as FirebaseAuth;
use App\Models\User;
use Exception;

class AuthController extends Controller
{
    protected FirebaseAuth $firebaseAuth;

    public function __construct(FirebaseAuth $firebaseAuth)
    {
        $this->firebaseAuth = $firebaseAuth;
    }

    public function firebaseLogin(Request $request)
    {
        $request->validate([
            'firebase_token' => 'required|string'
        ]);

        try {
            $token = $request->input('firebase_token');
            $verifiedIdToken = $this->firebaseAuth->verifyIdToken($token);
            
            // Get user data from Firebase token
            $uid = $verifiedIdToken->claims()->get('sub');
            $email = $verifiedIdToken->claims()->get('email');
            $name = $verifiedIdToken->claims()->get('name');

            // Find or create user in our database
            $user = User::findOrCreateByFirebaseUid($uid, [
                'email' => $email,
                'name' => $name,
            ]);

            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'firebase_uid' => $uid
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Invalid Firebase token',
                'message' => $e->getMessage()
            ], 401);
        }
    }
}
```

**What this does:**
- Accepts Firebase ID tokens from client applications
- Verifies tokens with Firebase
- Creates/finds users in your local database
- Returns user information for successful logins

---

## Step 10: Add Authentication Routes

### 10.1 Update `routes/api.php`:
```php
use App\Http\Controllers\AuthController;

// Authentication routes
Route::post('/auth/firebase-login', [AuthController::class, 'firebaseLogin']);

// Protected routes example
Route::middleware('firebase.auth')->group(function () {
    Route::post('vocabulary-entries/{id}/vote-example', [VocabularyEntryController::class, 'voteExample']);
    Route::post('vocabulary-sets/{id}/rate', [VocabularySetController::class, 'rate']);
});
```

**What this does:**
- Creates login endpoint for client applications
- Shows how to protect routes with Firebase authentication
- Groups protected routes for organization

---

## Step 11: Test Your Setup

### 11.1 Test Firebase connection:
```bash
curl http://127.0.0.1:8000/api/test
```
Expected: `{"ok": true}`

### 11.2 Test login endpoint:
```bash
curl -X POST http://127.0.0.1:8000/api/auth/firebase-login \
  -H "Content-Type: application/json" \
  -d '{"firebase_token": "fake-token"}'
```
Expected: Firebase validation error

### 11.3 Test protected route:
```bash
curl -X POST http://127.0.0.1:8000/api/vocabulary-entries/1/vote-example
```
Expected: `{"error": "Authorization token required"}`

---

## How It All Connects

### 1. **Client-Side Flow:**
```
User logs in → Firebase Auth → Client receives ID token → Send token to your API
```

### 2. **Server-Side Flow:**
```
API receives token → Middleware verifies with Firebase → Adds user info to request → Controller processes
```

### 3. **Database Integration:**
```
Firebase UID → Maps to local User record → Enables relationships with your app data
```

### 4. **File Relationships:**
- **firebase_credentials.json**: Contains private keys for Firebase Admin SDK
- **config/firebase.php**: Laravel config that points to credentials
- **AppServiceProvider.php**: Registers Firebase Auth service in container
- **FirebaseAuth middleware**: Protects routes and verifies tokens
- **AuthController**: Handles login and user creation
- **User model**: Links Firebase users to local database records

---

## Security Notes

1. **Never commit firebase_credentials.json to version control**
2. **Use environment variables for all sensitive data**
3. **Firebase handles password security - your API only verifies tokens**
4. **Tokens are short-lived and automatically expire**
5. **Always validate and sanitize user input**

---

## Production Considerations

1. **Use proper error logging for Firebase errors**
2. **Implement rate limiting on auth endpoints**
3. **Consider caching Firebase public keys for performance**
4. **Use HTTPS in production for token transmission**
5. **Monitor Firebase usage and costs**

---

This setup provides a complete Firebase Authentication integration that's secure, scalable, and follows Laravel best practices.