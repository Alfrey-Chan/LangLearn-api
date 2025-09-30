<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VocabularySet extends Model
{
    protected $fillable = ['language_code', 'user_id', 'difficulty', 'title', 'description', 'is_active', 'image_url', 'rating', 'category', 'views'];

    public function vocabularyEntries() 
    {
        return $this->belongsToMany(VocabularyEntry::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function tags() 
    {
        return $this->belongsToMany(Tag::class, 'vocabulary_set_tag');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function favouritedByUsers()
    {
        return $this->belongsToMany(User::class, 'user_favourites', 'vocabulary_set_id', 'firebase_uid', 'id', 'firebase_uid')->whereNotNull('user_favourites.vocabulary_set_id');
    }
}
