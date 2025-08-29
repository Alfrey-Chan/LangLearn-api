<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\FavouriteController;
use App\Http\Controllers\VocabularySetController;
use App\Http\Controllers\VocabularyEntryController;

// Authentication routes
Route::post('/auth/firebase-login', [AuthController::class, 'firebaseLogin']);

// Debug route to check Firebase config
Route::get('/debug/firebase', function() {
    return response()->json([
        'firebase_credentials_env' => env('FIREBASE_CREDENTIALS') ? 'SET' : 'NOT SET',
        'firebase_credentials_json_env' => env('FIREBASE_CREDENTIALS_JSON') ? 'SET' : 'NOT SET', 
        'file_exists' => file_exists('/app/storage/firebase/firebase_credentials.json') ? 'YES' : 'NO',
        'file_readable' => is_readable('/app/storage/firebase/firebase_credentials.json') ? 'YES' : 'NO'
    ]);
});

// Public test routes (no authentication required)
Route::get('/health', function() {
    return response()->json(['status' => 'OK', 'message' => 'API is running']);
});

Route::get('/vocabulary-sets/public', [VocabularySetController::class, 'index']);
Route::get('/languages/public', [LanguageController::class, 'index']);

// Protected routes (require Firebase authentication)
Route::middleware('firebase.auth')->group(function () {
    Route::apiResource('languages', LanguageController::class);
    Route::apiResource('vocabulary-sets', VocabularySetController::class);
    Route::apiResource('vocabulary-entries', VocabularyEntryController::class);
    Route::apiResource('favourites', FavouriteController::class);
    
    Route::post('vocabulary-entries/{id}/vote-example', [VocabularyEntryController::class, 'voteExample']);
    Route::post('vocabulary-sets/{id}/rate', [VocabularySetController::class, 'rate']);
    Route::post('vocabulary-sets/{id}/increment-views', [VocabularySetController::class, 'incrementViews']);

    Route::get('profile', [UserController::class, 'profile']);
    
    Route::get('quizzes', [QuizController::class, 'retrieveUserQuizzes']);
    Route::get('take-quiz/{id}', [QuizController::class, 'retrieveQuizcontents']);
    Route::post('submit-quiz', [QuizController::class, 'submitAnswers']);
});
    

