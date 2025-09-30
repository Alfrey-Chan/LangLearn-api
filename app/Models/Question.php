<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{   
    protected $fillable = ['type', 'question', 'target_word', 'options', 'word_bank', 'correct_answer',' acceptable_answers', 'requires_feedback', 'points'];

    protected $casts = [
        'word_bank' => 'array',
        'options' => 'array',
        'acceptable_answers' => 'array',
    ];
}
