<?php

namespace Database\Seeders;

use App\Models\VocabularySet;
use App\Models\VocabularyEntry;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class VocabularySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonString = file_get_contents(database_path('seeders/coffee_shop_vocab.json'));
        $data = json_decode($jsonString, true);

        // Create vocabulary set 
        $setData = $data['vocabulary_set'];
        $set = VocabularySet::create([
            'language_id' => 1,
            'title' => $setData['title'],
            'description' => $setData['description'],
            'type' => $setData['type'],
            'difficulty' => $setData['difficulty'],
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
                'sentence_examples' => json_encode($entryData['sentence_examples']),
                'dialogue_examples' => json_encode($entryData['dialogue_examples']),
                'additional_notes' => $entryData['additional_notes'],
            ]);

            // Link entry to set by inserting a record in the pivot table
            // ~ "This set contains this specific word entry"
            $set->vocabularyEntries()->attach($entry->id); 
        }
    }
}
