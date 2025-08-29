<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['category'];

    public function vocabularySets()
    {
        return $this->belongsToMany(VocabularySet::class);
    }

    public function subcategories()
    {
        return $this->hasMany(Subcategory::class);
    }
}
