<?php

namespace Database\Seeders;

use App\Models\DialogueExample;
use App\Models\SentenceExample;
use App\Models\VocabularySet;
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
        $jsonFiles = glob(database_path('seeders/data/*.json'));
        foreach ($jsonFiles as $filePath) {
            $jsonString = file_get_contents($filePath);
            $data = json_decode($jsonString, true); 

            // Create vocabulary set 
            $setData = $data['vocabulary_set'];
            $set = VocabularySet::create([
                'language_id' => $setData['language_id'],
                'title' => $setData['title'],
                'description' => $setData['description'],
                'type' => $setData['type'],
                'difficulty' => $setData['difficulty'],
                'image_url' => $setData['image_url'],
            ]);

            // Create entries
            foreach($data['entries'] as $entryData) {
                $entry = VocabularyEntry::create([
                    'language_id' => 1,
                    'word' => $entryData['word'],
                    'hiragana' => $entryData['hiragana'],
                    'romaji' => $entryData['romaji'],
                    'part_of_speech' => json_encode($entryData['part_of_speech']),
                    'meanings' => json_encode($entryData['meanings']),
                    'additional_notes' => $entryData['additional_notes'],
                    'related_words' => json_encode($entryData['related_words']),
                ]);

                // Create sentence examples
                foreach($entryData['sentence_examples'] as $sentenceExample) {
                    $sentenceExample = SentenceExample::create([
                        'vocabulary_entry_id' => $entry->id,
                        'sentence_data' => json_encode($sentenceExample),
                    ]);
                }

                // Create dialogue examples
                foreach($entryData['dialogue_examples'] as $dialogueExample) {
                    DialogueExample::create([
                        'vocabulary_entry_id' => $entry->id,
                        'dialogue_data' => json_encode($dialogueExample['example']), // Access the 'example' key
                    ]);
                }

                // Link entry to set by inserting a record in the pivot table
                // ~ "This set contains this specific word entry"
                $set->vocabularyEntries()->attach($entry->id); 
            }
            $set->tags()->attach([1, 3]); // 'daily conversation', 'restaurant' 
        }
        
    }
}
