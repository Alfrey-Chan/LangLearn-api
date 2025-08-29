<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\User;
use App\Models\QuizResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{   
    public function retrieveUserQuizzes(Request $request)
    {
        $firebaseUid = $request->attributes->get('firebase_uid');
        $user = User::where('firebase_uid', $firebaseUid)->first();

        $userQuizzes = $user->getUserQuizStats();

        return response()->json(['user_quiz_data' => $userQuizzes], 200);
    }

    public function retrieveQuizcontents(string $vocabSetId) 
    {
        $quiz = Quiz::with('questions')
            ->where('vocabulary_set_id', $vocabSetId)
            ->first();

        if (!$quiz) {
            return response()->json(['error' => 'Quiz with ID $vocabSetId not found.'], 404);
        }

        return response()->json($quiz, 200);
    }

    public function submitAnswers(Request $request)
    {   
        $firebaseUid = $request->attributes->get('firebase_uid');

        $request->validate([
            'quiz_id' => 'required|numeric',
            'score_percent' => 'required|numeric|min:0|max:100'
        ]);

        QuizResult::create([
            'quiz_id' => $request->quiz_id,
            'firebase_uid' => $firebaseUid,
            'score_percent' => $request->score_percent
        ]);

        $user = User::where('firebase_uid', $firebaseUid)->first();
        $user->updateUserStatistics();

        return response()->json(['message' => 'Quiz results recoreded successfully'], 201);
    }
}
