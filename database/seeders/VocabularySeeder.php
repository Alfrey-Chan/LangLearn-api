<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\VocabularySet;
use App\Models\SentenceExample;
use App\Models\VocabularyEntry;
use Illuminate\Database\Seeder;

class VocabularySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {   
        $directories = glob(database_path('./seeders/data/vocab_sets/*'), GLOB_ONLYDIR);
        
        foreach ($directories as $directory) {
            $dirName = basename($directory);
            $jsonFiles = glob($directory . '/*json');

            $setData = json_decode(file_get_contents($jsonFiles[0]), true);
            $entryData = json_decode(file_get_contents($jsonFiles[1]), true);
            $quizData = json_decode(file_get_contents($jsonFiles[2]), true);
            $examplesData = json_decode(file_get_contents($jsonFiles[3]), true);

            $set = $this->seedVocabularySet($setData);
            $this->seedVocabularyEntries($entryData, $set);
            $this->seedQuizzes($quizData, $set);
            $this->seedEntryExamples($examplesData);
        }
    }

    private function seedVocabularySet(array $data) : VocabularySet 
    {
        $set = VocabularySet::create([
            'language_code' => $data['language_code'],
            'title' => $data['title'],
            'description' => $data['description'],
            'difficulty' => $data['difficulty'],
            'image_url' => $data['image_url'],
            'category' => $data['category']
        ]);

        $tagIds = Tag::whereIn('tag_jp', $data['tags'])->pluck('id')->toArray();
        $set->tags()->sync($tagIds);
        return $set;
    }

    private function seedVocabularyEntries(array $data, VocabularySet $set) : void
    {
        $entryIds = [];
        foreach ($data['entries'] as $entryData) {
            $entry = VocabularyEntry::create([
                'language_code' => $entryData['language_code'],
                'word' => $entryData['word'],
                'hiragana' => $entryData['hiragana'],
                'romaji' => $entryData['romaji'],
                'pinyin' => $entryData['pinyin'],
                'part_of_speech' => $entryData['part_of_speech'],
                'meanings' => $entryData['meanings'],
                'additional_notes' => $entryData['additional_notes'] ?? null,
            ]);
            $entryIds[] = $entry->id;
        }
        $set->vocabularyEntries()->sync($entryIds);
    }

    private function seedEntryExamples(array $data) : void
    {
        foreach ($data as $word => $sentences) {
            $vocabularyEntry = VocabularyEntry::where("word", $word)->first();
            if ($vocabularyEntry) {
                foreach ($sentences as $sentence) {
                    SentenceExample::create([
                        'vocabulary_entry_id' => $vocabularyEntry->id,
                        'sentence_original' => $sentence["original"],
                        'sentence_translated' => $sentence["translated"],
                    ]);
                }
            }
        }
    }

    private function seedQuizzes(array $data, VocabularySet $set) : void
    {
        $quiz = Quiz::create([
            'vocabulary_set_id' => $set->id,
            'title' => $set->title,
            'version' => 1.0,
        ]);

        foreach ($data as $question) {
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