<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\User;
use App\Models\QuizResult;
use App\Models\VocabularySet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\OpenAIService;

class QuizController extends Controller
{   
    protected $openaiService;

    public function __construct(OpenAIService $openaiService)
    {
        $this->openaiService = $openaiService; 
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
            return response()->json(['error' => 'Quiz with ID $vocabSetId not found.'], 404);
        }
        // Log::info('Quiz Structure: ' . json_encode($quiz, JSON_PRETTY_PRINT));
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

    public function submitAnswers(Request $request)
    {
        $firebaseUid = $request->attributes->get('firebase_uid');

        $questions = $request->input('questions', []);

        if (empty($questions)) {
            return response()->json(['error' => 'No questions provided'], 400);
        }

        $quizId = $questions[0]['quiz_id'] ?? null;
        if (!$quizId) {
            return response()->json(['error' => 'Quiz ID not found in questions'], 400);
        }

        $fillQuestions = collect($questions)->whereIn('type', ['translation', 'sentence_creation'])->toArray();

        $feedbacks = [];
        if (!empty($fillQuestions)) {
            try {
                $feedbacks = $this->openaiService->submitQuizAnswers($fillQuestions);
            } catch (\Exception $e) {
                Log::error('OpenAI service error: ' . $e->getMessage());
                return response()->json(['error' => 'Failed to process AI feedback'], 500);
            }
        }

        foreach ($questions as &$question) {
            if (in_array($question['type'], ['translation', 'sentence_creation'])) {
                $aiFeedback = collect($feedbacks['result'] ?? [])->firstWhere('id', $question['id']);
                $question['feedback'] = $aiFeedback ?? ['points_awarded' => 0, 'feedback' => 'AI feedback unavailable'];
            }
        }

        $results = $this->calculateScore($questions);

        QuizResult::create([
            'quiz_id' => $quizId,
            'firebase_uid' => $firebaseUid,
            'score_percent' => $results['percentage'],
        ]);

        $user = User::where('firebase_uid', $firebaseUid)->first();
        $user->updateUserStatistics();

        return response()->json([
                'message' => 'Quiz results recorded successfully',
                'results' => $results
            ], 201);
    }

    private function calculateScore($questions) {
        $totalPossibleScore = 0;
        $score = 0;

        foreach ($questions as &$question) {
            $points = $question['points'] ?? 0;
            $totalPossibleScore += $points;
            
            $type = $question['type'];
            $userAnswer = $question['user_answer'] ?? '';
            $correctAnswer = $question['correct_answer'] ?? '';

            if ($type === 'translation' || $type === 'sentence_creation') {
                // AI - graded questions
                $score += $question['feedback']['points_awarded'] ?? 0;
            } else if (($type === "multiple_choice" || $type === 'fill_blank') && $userAnswer === $correctAnswer) {
                // MC questions
                $score += $points;
                $question['is_correct'] = true;
            } else if ($type === "word_rearrangement" && $userAnswer === implode(" ", $question['word_bank'] ?? [])) { // correct answer is word_bank joined together
                // Word rearrangement questions
                $score += $points;
                $question['is_correct'] = true;
            } else {
                $question['is_correct'] = false;
            }
        }

        $percentage = round(($score/$totalPossibleScore) * 100, 1);
        return [
            'score' => $score,
            'totalPossible' => $totalPossibleScore,
            'percentage' => $percentage,
            'questions' => $questions
        ];
    }
}
