<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserFavourite;
use Illuminate\Support\Facades\Log;

class FavouriteController extends Controller
{
    /**
     * Get user's favorites (entries and sets)
     */
    public function index(Request $request)
    {
        $firebaseUid = $request->attributes->get('firebase_uid');
        
        $favouritedEntries = UserFavourite::where('firebase_uid', $firebaseUid)
            ->whereNotNull('vocabulary_entry_id')
            ->with('vocabularyEntry')
            ->get()
            ->pluck('vocabularyEntry');
            
        $favouritedSets = UserFavourite::where('firebase_uid', $firebaseUid)
            ->whereNotNull('vocabulary_set_id')
            ->with(['vocabularySet' => function($query) {
                $query->withCount('vocabularyEntries as vocabulary_entries_count');
            }])
            ->get()
            ->pluck('vocabularySet');
            
        return response()->json([
            'vocabulary_entries' => $favouritedEntries,
            'vocabulary_sets' => $favouritedSets
        ]);
    }

    /**
     * Add to favorites
     */
    public function store(Request $request)
    {
        $firebaseUid = $request->attributes->get('firebase_uid');
        
        $request->validate([
            'vocabulary_entry_id' => 'nullable|exists:vocabulary_entries,id',
            'vocabulary_set_id' => 'nullable|exists:vocabulary_sets,id'
        ]);

        // Ensure only one of entry_id or set_id is provided
        if ((!$request->vocabulary_entry_id && !$request->vocabulary_set_id) ||
            ($request->vocabulary_entry_id && $request->vocabulary_set_id)) {
            return response()->json(['error' => 'Provide either vocabulary_entry_id OR vocabulary_set_id, not both'], 400);
        }

        $favourite = UserFavourite::updateOrCreate([
            'firebase_uid' => $firebaseUid,
            'vocabulary_entry_id' => $request->vocabulary_entry_id ?? null,
            'vocabulary_set_id' => $request->vocabulary_set_id ?? null
        ]);

        return response()->json($favourite, 201);
    }

    /**
     * Remove from favorites
     */
    public function destroy(Request $request)
    {
        $firebaseUid = $request->attributes->get('firebase_uid');
        
        $request->validate([
            'vocabulary_entry_id' => 'nullable|exists:vocabulary_entries,id',
            'vocabulary_set_id' => 'nullable|exists:vocabulary_sets,id'
        ]);
        
        $deletedCount = UserFavourite::where('firebase_uid', $firebaseUid)
            ->where('vocabulary_entry_id', $request->vocabulary_entry_id ?? null)
            ->where('vocabulary_set_id', $request->vocabulary_set_id ?? null)
            ->delete();

        Log::Info('Deleted count: ' . $deletedCount);

        return response()->json(['message' => 'Removed from favorites'], 200);
    }
}
