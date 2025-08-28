<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFavourite extends Model
{
    protected $fillable = ['firebase_uid', 'vocabulary_entry_id', 'vocabulary_set_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'firebase_uid', 'firebase_uid');
    }

    public function vocabularyEntry()
    {
        return $this->belongsTo(VocabularyEntry::class);
    }

    public function vocabularySet()
    {
        return $this->belongsTo(VocabularySet::class);
    }
}