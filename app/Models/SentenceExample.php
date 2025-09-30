<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SentenceExample extends Model
{
    protected $fillable = ['vocabulary_entry_id', 'sentence_original', 'sentence_translated'];


    public function vocabularyEntry() 
    {
        return $this->belongsTo(VocabularyEntry::class);
    }
}
