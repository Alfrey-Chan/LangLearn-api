<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DialogueExample extends Model
{
    protected $fillable = ['vocabulary_entry_id', 'dialogue_data', 'upvotes', 'downvotes'];

    public function vocabularyEntry()
    {
        return $this->belongsTo(VocabularyEntry::class);
    }

    public function votes()
    {
        return $this->hasMany(ExampleVote::class, 'example_id')
                    ->where('example_type', 'dialogue');
    }

    public function addVote(string $userId, string $voteType)
    {
        
        $this->votes()->updateOrCreate(
            ['user_id' => $userId], // search criteria
            [   
                'vote_type' => $voteType, 
                'example_type' => 'dialogue'
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
