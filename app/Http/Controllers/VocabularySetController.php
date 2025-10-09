<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;
use App\Models\UserFavourite;
use App\Models\VocabularySet;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class VocabularySetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {   
        $vocabularySets = VocabularySet::withCount('vocabularyEntries')->get();

        // current user firebase uid
        $firebaseUid = $request->attributes->get('firebase_uid');
        
        if ($firebaseUid) {
            $favouritedSetIds = UserFavourite::where('firebase_uid', $firebaseUid)
                ->whereNotNull('vocabulary_set_id')
                ->pluck('vocabulary_set_id')
                ->toArray();

            $vocabularySets->transform(function ($set) use ($favouritedSetIds) {
                $set->is_favourited = in_array($set->id, $favouritedSetIds);
                return $set;
            });
        } else {
            // no user logged in - mark all as not favourited (safety net)
            $vocabularySets->transform(fn($set) => $set->is_favourited = false);
        } 

        return $vocabularySets;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'language_id' => 'required|integer',
            'user_id' => 'nullable|integer',
            'type' => ['required', Rule::in(['premade', 'custom'])],
            'difficulty' => ['required', Rule::in(['beginner', 'intermediate', 'advanced'])], // alternative = 'in:beginner,intermediate,advanced'
            'title' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        $set = VocabularySet::create($validated);
        return response()->json($set, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $set = VocabularySet::with(['vocabularyEntries', 'tags'])->findOrFail($id);
        $set->total_ratings = $set->ratings()->count();

        $firebaseUid = $request->attributes->get('firebase_uid');
        if ($firebaseUid) {
            // User's rating for this set
            $userRating = $set->ratings()
                ->where('user_id', $firebaseUid)
                ->first();
            $set->user_rating = $userRating ? $userRating->rating : null;

            // Check if set is favorited
            $setFavorited = UserFavourite::where('firebase_uid', $firebaseUid)
                ->where('vocabulary_set_id', $id)
                ->exists();
            $set->isFavourited = $setFavorited;

            // Get favorited vocabulary entry IDs for this user
            $favoritedEntryIds = UserFavourite::where('firebase_uid', $firebaseUid)
                ->whereNotNull('vocabulary_entry_id')
                ->pluck('vocabulary_entry_id')
                ->toArray();

            // Add isFavourited to each vocabulary entry
            $set->vocabularyEntries->transform(function ($entry) use ($favoritedEntryIds) {
                $entry->isFavourited = in_array($entry->id, $favoritedEntryIds);
                return $entry;
            });
        }
        return $set;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {   
        $set = VocabularySet::findOrFail($id);
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',           
            'description' => 'nullable|string',               
            'difficulty' => 'nullable|in:beginner,intermediate,advanced',
            'type' => 'nullable|in:premade,custom',
            'is_active' => 'nullable|boolean'
        ]);

        $set->update($validated);
        return response()->json($set, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        VocabularySet::findOrFail($id)->delete();
        return response()->json(['message' => "Vocabulary set with id $id successfully deleted."], 204);
    }

    public function incrementViews(string $id)
    {
        $set = VocabularySet::findOrFail($id);
        $set->increment('views');
        return response()->json(['views' => $set->views], 200);
    }

    public function rate(Request $request, string $id)
    {   
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $set = VocabularySet::findOrFail($id);
        $firebaseUid = $request->attributes->get('firebase_uid');

        $rating = Rating::updateOrCreate(
            [
                'user_id' => $firebaseUid,
                'vocabulary_set_id' => $id
            ],
            [
                'rating' => $validated['rating']
            ]
        );

        $avgRating = $set->ratings()->avg('rating');
        $set->update(['rating' => round($avgRating, 2)]);

        return response()->json([
            'average_rating' => $set->fresh()->rating,
            'total_ratings' => $set->ratings()->count()
        ], 200);
    }

    public function getUserRating(string $vocabularySetId, string $userId) 
    {
        $rating = Rating::where('vocabulary_set_id', $vocabularySetId)->where('user_id', $userId)->first();

        return $rating;
    }
}
