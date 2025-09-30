<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('./seeders/data/vocab_sets/shopping_&_retail/c_shopping_&_retail.json');
        $data = json_decode(file_get_contents($path), true);

        $quiz = Quiz::create([
            'vocabulary_set_id' => 1,
            'title' => 'Shopping & Retail',
            'version' => 1.0,
        ]);

        foreach ($data["questions"] as $question) {
            Question::create([
                'quiz_id' => $quiz->id,
                'type' => $question['type'],
                'question' => $question['question'],
                "target_word" => $question['target_word'],
                "options" => $question['options'],
                "word_bank" => $question['word_bank'],
                "correct_answer" => strtolower($question['correct_answer']),
                "acceptable_answers" => $question['acceptable_answers'],
                "requires_feedback" => $question['requires_feedback'],
                "points" => $question['points'],
            ]);
        }
    }
}
