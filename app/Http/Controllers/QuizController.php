<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\User;
use App\Models\QuizResult;
use App\Models\VocabularySet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\OpenAIService;
use App\Http\Requests\StoreQuizAnswersRequest;
use App\Services\QuizResultService;

class QuizController extends Controller
{   
    protected $openaiService;
    protected $quizResultService;

    public function __construct(OpenAIService $openaiService, QuizResultService $quizResultService)
    {
        $this->openaiService = $openaiService; 
        $this->quizResultService = $quizResultService;
    }

    public function retrieveUserQuizzes(Request $request)
    {
        $firebaseUid = $request->attributes->get('firebase_uid');
        $user = User::where('firebase_uid', $firebaseUid)->first();
        $userQuizzes = $user->getUserQuizStats();

        $vocabSets = [];
        foreach($userQuizzes as $quiz) {
            $setId = $quiz->quiz->vocabulary_set_id;
            $set = VocabularySet::where('id', $setId)->first();
            $set['quiz'] = $quiz;
            array_push($vocabSets, $set);
        }

        return response()->json(['user_quizzes_data' => $userQuizzes], 200);
    }

    public function retrieveQuiz(string $vocabSetId) 
    {
        $quiz = Quiz::with('questions')
            ->where('vocabulary_set_id', $vocabSetId)
            ->first()
            ->toArray();

        if (!$quiz) {
            return response()->json(['error' => "Quiz with ID $vocabSetId not found."], 404);
        }

        $restructuredQuestions = [];

        foreach ($quiz['questions'] as $question) {
            $targetWord = $question['target_word'];
            $type = $question['type'];

            $restructuredQuestions[$targetWord][$type][] = $question;
        }

        $restructuredQuiz = [
            "id" => $quiz['id'],
            "vocabulary_set_id" => $quiz['vocabulary_set_id'],
            "title" =>  $quiz['title'],
            "version" => $quiz['version'],
            "questions" => $restructuredQuestions,
        ];

        return response()->json($restructuredQuiz, 200);
    }

    public function submitAnswers(StoreQuizAnswersRequest $request)
    {
        $firebaseUid = $request->attributes->get('firebase_uid');
        $questions = $request->validated()['questions'];

        try {
            $results = $this->quizResultService->processQuizSubmission($questions, $firebaseUid);
            return response()->json([
                'message' => 'Quiz results recorded successfully',
                'results' => $results
            ], 201);
        } catch (\Exception $e) {
            Log::error('Quiz submission error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to process quiz submission'
            ], 500);
        }
    }
}
