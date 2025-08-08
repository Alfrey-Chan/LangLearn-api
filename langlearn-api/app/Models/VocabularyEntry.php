<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VocabularyEntry extends Model
{
    protected $fillable = ['language_id', 'word', 'hiragana', 'romaji', 'pinyin', 'meanings', 'sentence_examples', 'dialogue_examples', 'additional_notes'];

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function vocabularySets()
    {
        return $this->belongsToMany(VocabularySet::class);
    }
}
