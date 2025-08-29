<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStatistic extends Model
{
    protected $primaryKey = 'firebase_uid';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = ['firebase_uid', 'last_activity_date', 'current_streak', 'longest_streak', 'total_quizzes', 'average_score'];

    public function user()
    {
        return $this->belongsTo(User::class, 'firebase_uid', 'firebase_uid');
    }
}
