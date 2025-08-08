<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VocabularySet extends Model
{
    protected $fillable = ['language_id', 'user_id', 'type', 'difficulty', 'title', 'description', 'is_active'];

    public function vocabularyEntries() 
    {
        return $this->belongsToMany(VocabularyEntry::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tags() 
    {
        return $this->belongsToMany(Tag::class);
    }
}
