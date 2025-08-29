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

// Public routes
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
    

