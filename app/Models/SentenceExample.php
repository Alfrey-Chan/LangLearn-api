<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SentenceExample extends Model
{
    protected $fillable = ['vocabulary_entry_id', 'sentence_data', 'upvotes', 'downvotes'];

    protected $casts = [
        'sentence_data' => 'array',
    ];

    public function vocabularyEntry() 
    {
        return $this->belongsTo(VocabularyEntry::class);
    }

    public function votes()
    {
        return $this->hasMany(ExampleVote::class, 'example_id') // give me all vote records where example_id = this sentenceExample's Id
                    ->where('example_type', 'sentence');
    }

    public function addVote(string $userId, string $voteType)
    {
        
        $this->votes()->updateOrCreate(
            ['user_id' => $userId], // search criteria
            [   
                'vote_type' => $voteType, 
                'example_type' => 'sentence'
            ],
        );

        $this->updateVoteCounts();
    }

    public function updateVoteCounts()
    {
        $this->update([
            'upvotes' => $this->votes()->where('vote_type', 'upvote')->count(),
            'downvotes' => $this->votes()->where('vote_type', 'downvote')->count()
        ]);
    }
}
