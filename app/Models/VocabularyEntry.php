<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VocabularyEntry extends Model
{
    protected $fillable = [
        'language_id', 
        'word', 
        'hiragana', 
        'romaji', 
        'pinyin', 
        'meanings', 
        'additional_notes'
    ];

    protected $casts = [
      'part_of_speech' => 'array',
      'meanings' => 'array',
      'related_words' => 'array',
    ];

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function vocabularySets()
    {
        return $this->belongsToMany(VocabularySet::class);
    }

    public function sentenceExamples()
    {
        return $this->hasMany(SentenceExample::class);
    }

    public function dialogueExamples()
    {
        return $this->hasMany(DialogueExample::class);
    }
}
