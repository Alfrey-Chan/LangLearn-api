<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\VocabularySetController;
use App\Http\Controllers\VocabularyEntryController;
use App\Http\Controllers\AuthController;

// Authentication routes
Route::post('/auth/firebase-login', [AuthController::class, 'firebaseLogin']);

// Public routes
Route::middleware('firebase.auth')->group(function () {
    Route::apiResource('languages', LanguageController::class);
    Route::apiResource('vocabulary-sets', VocabularySetController::class);
    Route::apiResource('vocabulary-entries', VocabularyEntryController::class);
    Route::post('vocabulary-entries/{id}/vote-example', [VocabularyEntryController::class, 'voteExample']);
    Route::post('vocabulary-sets/{id}/rate', [VocabularySetController::class, 'rate']);

    // Route::get('profile', [UserController::class, 'profile']);
});
