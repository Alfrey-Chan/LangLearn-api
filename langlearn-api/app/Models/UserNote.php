<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNote extends Model
{
    protected $fillable = ["user_id", "item_id", "item_type", "save_type"];

    public function user() 
    {
        return $this->belongsTo(User::class);
    }

    public function item() 
    {   
        /* 
            Looks fields with 'item' in it's name.
            item_type -> "vocabulary_set" or "vocabulary_entry" table to reference
            item_id -> record id

            $userNote = UserNote::first();
            echo $userNote->item;
        */
        return $this->morphTo(); 
    }
}
