<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = ['code', 'name', 'native_name', 'is_active'];

    public function vocabularySets()
    {
        return $this->hasMany(VocabularySet::class);
    }

    public function vocabularyEntries() 
    {
        return $this->hasMany(VocabularyEntry::class);
    }
}
