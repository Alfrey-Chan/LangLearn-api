<?php

namespace App\Services;

use App\Models\User;
use App\Models\QuizResult;
use CuyZ\Valinor\Type\Types\ArrayType;
use Illuminate\Support\Facades\Log;

class QuizResultService
{
    protected $openaiService;

    public function __construct(OpenAIService $openaiService)
    {
        $this->openaiService = $openaiService;
    }

    public function processQuizSubmission(array $questions, string $firebaseUid): array
    {
        $questionsWithFeedback = $this->addAIFeedback($questions);

        $results = $this->calculateScore($questionsWithFeedback);

        $this->saveQuizResult($questions, $firebaseUid, $results['percentage']);

        $this->updateUserStatistics($firebaseUid);

        return $results;
    }

    /**
     * Add AI feedback to translation and sentence creation type questions
     */
    private function addAIFeedback(array $questions): array
    {
        $fillQuestions = collect($questions)->whereIn('type', ['translation', 'sentence_creation'])->toArray();

        if (empty($fillQuestions)) {
            return $questions; // just return if all multiple choice questions 
        }

        try {
            $feedbacks = $this->openaiService->submitQuizAnswers($fillQuestions);

            foreach ($questions as &$question) {
                if (in_array($question['type'], ['translation', 'sentence_creation'])) {
                    $aiFeedback = collect($feedbacks['result'] ?? [])->firstWhere('id', $question['id']);
                    $question['feedback'] = $aiFeedback ?? $this->getDefaultFeedback();
                }
            }
        } catch (\Exception $e) {
            Log::error('OpenAI service error: ' . $e->getMessage());

            // Fallback feedback if openai call encounters errors
            foreach ($questions as &$question) {
                if (in_array($question['type'], ['translation', 'sentence_creation'])) {
                    $question['feedback'] = $this->getDefaultFeedback('AI feedback temporarily unavailable');
                }
            }
        }

        return $questions;
    }

    private function calculateScore(array $questions): array
    {
        $totalPossibleScore = 0;
        $score = 0;

        foreach ($questions as &$question) {
            $points = $question['points'] ?? 0;
            $totalPossibleScore += $points;

            $score += $this->scoreIndividualQuestion($question);
        }

        $percentage = $totalPossibleScore > 0 ? round(($score / $totalPossibleScore) * 100, 1) : 0;

        return [
            'score' => $score,
            'totalPossible' => $totalPossibleScore,
            'percentage' => $percentage,
            'questions' => $questions
        ];
    }

    private function scoreIndividualQuestion(array &$question): int
    {
        $type = $question['type'];
        $userAnswer = $question['user_answer'] ?? '';
        $correctAnswer = $question['correct_answer'] ?? ''; // will be empty string if it's a non MC type question
        $points = $question['points'] ?? 0;

        if ($type === 'translation' || $type === 'sentence_creation') {
            // AI-graded questions
            return $question['feedback']['points_awarded'] ?? 0;
        }

        if (($type === 'multiple_choice' || $type === 'fill_blank') && $userAnswer === $correctAnswer) {
            // Multiple choice questions
            $question['is_correct'] = true;
            return $points;
        }
            // Word-rearrangement questions
        if ($type === 'word_rearrangement' && $userAnswer === implode(' ', $question['word_bank'] ?? [])) {
            $question['is_correct'] = true;
            return $points;
        }

        $question['is_correct'] = false;
        return 0;
    }

    private function saveQuizResult(array $questions, string $firebaseUid, float $percentage): void
    {
        QuizResult::create([
            'quiz_id' => $questions[0]['quiz_id'],
            'firebase_uid' => $firebaseUid,
            'score_percent' => $percentage,
        ]);
    }

    private function updateUserStatistics(string $firebaseUid): void
    {
        $user = User::where('firebase_uid', $firebaseUid)->first();
        if ($user) {
            $user->updateUserStatistics();
        }
    }

    /**
    * Get default feedback for openAI service fails
    */
    private function getDefaultFeedback(string $message = 'AI feedback unavailable'): array
    {
        return [
            'points_awarded' => 0,
            'feedback' => $message
        ];
    }
}
