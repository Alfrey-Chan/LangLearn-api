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
        $validated = $request->validate([
            'id' => 'required|integer',
            'type' => 'required|in:vocabulary_set,vocabulary_entry'
        ]);
        $id = $validated["id"];
        $type = $validated["type"];
        $firebaseUid = $request->attributes->get('firebase_uid');
        $column = $type . "_id";

        $favourite = UserFavourite::create([
            'firebase_uid' => $firebaseUid,
            $column => $id,
        ]);

        return response()->json($favourite, 201);
    }

    /**
     * Remove from favourites
     */
    public function destroy(Request $request) 
    {   
        $validated = $request->validate([
            "id" => "required|integer",
            "type" => "in:vocabulary_set,vocabulary_entry|required|string",
        ]);
        $id = $validated["id"];
        $type = $validated["type"];
        $firebaseUid = $request->attributes->get('firebase_uid');
        $column = $type === "vocabulary_set" ? "vocabulary_set_id" : "vocabulary_entry_id";

        UserFavourite::where('firebase_uid', $firebaseUid)
            ->where($column, $id)
            ->delete();

        return response()->json(['message' => "$type removed from favourites"], 200);
    }
}
