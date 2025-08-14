<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExampleVote extends Model
{
    protected $fillable = ['user_id', 'example_id', 'example_type', 'vote_type'];

    public function sentenceExample()
    {
        return $this->belongsTo(SentenceExample::class, 'example_id')->where('example_type', 'sentence');
    }

    public function dialogueExample()
    {
        return $this->belongsTo(DialogueExample::class, 'example_id')->where('example_type', 'dialogue');
    }
}
