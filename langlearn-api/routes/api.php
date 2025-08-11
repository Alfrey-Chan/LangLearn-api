<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\VocabularySetController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::apiResource('languages', LanguageController::class);
Route::apiResource('vocabulary-sets', VocabularySetController::class);
Route::post('vocabulary-sets/{id}/rate', [VocabularySetController::class, 'rate']);
