<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = ['vocabulary_set_id', 'title', 'version'];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function results()
    {
        return $this->hasMany(QuizResult::class);
    }

    public function getAverageScore()
    {
        return $this->results()->avg('score_percent');
    }

    public function getUserAverageScore($firebaseUid)
    {
        return $this->results()
            ->where('firebase_uid', $firebaseUid)
            ->avg('score_percent');
    }
}
