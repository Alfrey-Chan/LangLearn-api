<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;
use App\Models\VocabularySet;
use Illuminate\Validation\Rule;

class VocabularySetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return VocabularySet::withCount('vocabularyEntries')->get();
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
    public function show(string $id)
    {
        return VocabularySet::with(['vocabularyEntries', 'tags'])->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {   
        $set = VocabularySet::findOrFail($id);
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',           // Optional
            'description' => 'nullable|string',             // Optional  
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
        return response()->json(['message' => `Vocabulary set with id $id successfully deleted.`], 204);
    }

    public function incrementViews(string $id)
    {
        $set = VocabularySet::findOrFail($id);
        $set->increment('views');
        return response()->json($set, 200);
    }

    public function rate(Request $request, string $id)
    {   
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'user_id' => 'required|string'
        ]);

        $set = VocabularySet::findOrFail($id);

        $rating = Rating::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'vocabulary_set_id' => $id
            ],
            [
                'rating' => $request->rating
            ]
        );

        $avgRating = $set->ratings()->avg('rating');
        $set->update(['rating' => round($avgRating, 2)]);

        return response()->json([
            'average_rating' => $set->fresh()->rating
        ], 200);
    }

    public function getUserRating(string $vocabularySetId, string $userId) 
    {
        $rating = Rating::where('vocabulary_set_id', $vocabularySetId)->where('user_id', $userId)->first();

        return $rating;
    }
}
