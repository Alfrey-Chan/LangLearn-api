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

// Protected routes (require Firebase authentication)
Route::middleware('firebase.auth')->group(function () {
    Route::apiResource('languages', LanguageController::class);
    Route::apiResource('vocabulary-sets', VocabularySetController::class);
    Route::apiResource('vocabulary-entries', VocabularyEntryController::class);

    // Favourites
    Route::get('favourites', [FavouriteController::class, 'index']);
    Route::post('favourites', [FavouriteController::class, 'store']);
    Route::delete('favourites', [FavouriteController::class, 'destroy']);
    
    Route::post('vocabulary-entries/{id}/vote-example', [VocabularyEntryController::class, 'voteExample']);
    Route::post('vocabulary-sets/{id}/rate', [VocabularySetController::class, 'rate']);
    Route::post('vocabulary-sets/{id}/increment-views', [VocabularySetController::class, 'incrementViews']);

    Route::get('profile', [UserController::class, 'profile']);
    
    Route::get('quizzes/{vocabSetId}', [QuizController::class, 'retrieveQuiz']);
    Route::get('my-quizzes', [QuizController::class, 'retrieveUserQuizzes']);
    Route::post('submit-quiz', [QuizController::class, 'submitAnswers']);
});
    

