<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'firebase_uid',
        'email',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    /**
     * Find or create a user by Firebase UID
     */
    public static function findOrCreateByFirebaseUid(string $uid, array $userData = []): User
    {
        $user = static::where('firebase_uid', $uid)->first();
        
        if (!$user) {
            $user = static::create([
                'firebase_uid' => $uid,
                'name' => $userData['name'] ?? '',
                'email' => $userData['email'] ?? '',
            ]);
        }
        
        return $user;
    }

    public function userStats() 
    {
        return $this->hasOne(UserStatistic::class, 'firebase_uid', 'firebase_uid');
    }

    // Relationship to get user's favorites
    public function favourites()
    {
        return $this->hasMany(UserFavourite::class, 'firebase_uid', 'firebase_uid');
    }

    // Get user's favorited vocabulary entries
    public function favouritedVocabularyEntries()
    {
        return $this->belongsToMany(VocabularyEntry::class, 'user_favourites', 'firebase_uid', 'vocabulary_entry_id', 'firebase_uid', 'id')
            ->whereNotNull('user_favourites.vocabulary_entry_id');
    }

    // Get user's favorited vocabulary sets
    public function favouritedVocabularySets()
    {
        return $this->belongsToMany(VocabularySet::class, 'user_favourites', 'firebase_uid', 'vocabulary_set_id', 'firebase_uid', 'id')
            ->whereNotNull('user_favourites.vocabulary_set_id');
    }

    // Get user's quiz results with quiz info
    public function quizResults()
    {
        return $this->hasMany(QuizResult::class, 'firebase_uid', 'firebase_uid');
    }

    // Get last quiz score for a specific quiz
    public function getLastQuizScore($quizId)
    {
        return $this->quizResults()
            ->where('quiz_id', $quizId)
            ->latest()
            ->first()?->score_percent;
    }

    // Get average score for a specific quiz
    public function getAverageQuizScore($quizId)
    {
        return $this->quizResults()
            ->where('quiz_id', $quizId)
            ->avg('score_percent');
    }

    // Get all quizzes user has taken with their stats
    public function getUserQuizStats()
    {
        return $this->quizResults()
            ->with('quiz')
            ->selectRaw('quiz_id, AVG(score_percent) as avg_score, MAX(score_percent) as best_score, COUNT(*) as attempts, MAX(created_at) as last_attempt, 
            (SELECT score_percent FROM quiz_results qr2 WHERE qr2.firebase_uid = quiz_results.firebase_uid 
            AND qr2.quiz_id = quiz_results.quiz_id ORDER BY qr2.created_at DESC LIMIT 1) as last_score')
            ->groupBy('quiz_id')
            ->get();
    }

    // Update user statistics after completing a quiz
    public function updateUserStatistics()
    {
        $totalQuizzes = $this->quizResults()->count();
        $averageScore = $this->quizResults()->avg('score_percent') ?? 0.00;
        
        $result = $this->userStats()->updateOrCreate(
            ['firebase_uid' => $this->firebase_uid],
            [
                'total_quizzes' => $totalQuizzes,
                'average_score' => round($averageScore, 2)
            ]
        );
    }
}
