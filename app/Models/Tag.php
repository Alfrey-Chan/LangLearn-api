<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ["name"];

    public function vocabularySets()
    {
        return $this->belongsToMany(VocabularySet::class);
    }

    public function vocabularyEntries()
    {
        return $this->belongsToMany(VocabularyEntry::class);
    }
}
