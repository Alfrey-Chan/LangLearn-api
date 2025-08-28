<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\VocabularySet;
use App\Models\DialogueExample;
use App\Models\SentenceExample;
use App\Models\VocabularyEntry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class VocabularySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {   
        $jsonFiles = glob(database_path('seeders/data/vocab_sets/*.json'));
        foreach ($jsonFiles as $filePath) {
            $jsonString = file_get_contents($filePath);
            $data = json_decode($jsonString, true); 

            // Create vocabulary set 
            $setData = $data['vocabulary_set'];
            $set = VocabularySet::create([
                'language_id' => $setData['language_id'],
                'title' => $setData['title'],
                'description' => $setData['description'],
                'difficulty' => $setData['difficulty'],
                'image_url' => $setData['image_url'],
            ]);
            $tagIds = Tag::whereIn('tag', $setData['tags'])->pluck('id')->toArray();
            $set->tags()->sync($tagIds);
            $this->seedQuizzes($set->id, $filePath);

            // Create entries
            foreach($data['entries'] as $entryData) {
                $entry = VocabularyEntry::create([
                    'language_id' => 2,
                    'word' => $entryData['word'],
                    'hiragana' => $entryData['hiragana'],
                    'romaji' => $entryData['romaji'],
                    'part_of_speech' => $entryData['part_of_speech'],
                    'meanings' => $entryData['meanings'],
                    'additional_notes' => $entryData['additional_notes'],
                    'related_words' => $entryData['related_words'],
                ]);

                // Create sentence examples
                foreach($entryData['sentence_examples'] as $sentenceExample) {
                    $sentenceExample = SentenceExample::create([
                        'vocabulary_entry_id' => $entry->id,
                        'sentence_data' => $sentenceExample,
                    ]);
                }

                // Create dialogue examples
                foreach($entryData['dialogue_examples'] as $dialogueExample) {
                    DialogueExample::create([
                        'vocabulary_entry_id' => $entry->id,
                        'dialogue_data' => $dialogueExample['example'], // Access the 'example' key
                    ]);
                }

                // Link entry to set by inserting a record in the pivot table
                // ~ "This set contains this specific word entry"
                $set->vocabularyEntries()->attach($entry->id); 
            }
        }
    }

    protected function seedQuizzes($vocabSetId, $vocabSetFilePath)
    {
        $baseName = pathinfo($vocabSetFilePath, PATHINFO_FILENAME);
        $quizFilePath = database_path("seeders/data/quizzes/{$baseName}_quiz.json");

        if (!file_exists($quizFilePath)) {
            Log::warning("Quiz file not found: {$quizFilePath}");
            return;
        }

        $jsonString = file_get_contents($quizFilePath);
        $data = json_decode($jsonString, true);

        $quiz = Quiz::create([
            "vocabulary_set_id" => $vocabSetId,
            "title" => $data["title"],
            "version" => $data["version"],
        ]);

        foreach ($data["items"] as $item) {
            Question::create([
                "quiz_id" => $quiz->id,
                "items" => $item,
            ]);
        }
    }
}
