<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DialogueExample;
use App\Models\SentenceExample;
use App\Models\VocabularyEntry;
use Illuminate\Validation\Rule;

class VocabularyEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return VocabularyEntry::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'language_id' => ['required', Rule::in([1, 2, 3])],
            'part_of_speech' => 'required|array:min:1',
            'word' => 'required|string|max:100',
            'hiragana' => 'nullable|string',
            'romaji' => 'nullable|string', 
            'pinyin' => 'nullable|string',

            'meanings' => 'required|array|min:1',
            'meanings.*.short' => 'required|string|max:100',
            'meanings.*.long' => 'required|string|max:500',

            'sentence_examples' => 'required|array|min:3',
            'sentence_examples.*.example' => 'required|string|max:500',
            'sentence_examples.*.example.*.translations' => 'required|array|min:4',
            'sentence_examples.*.example.*.translation' => 'required|string|max:750',
            'sentence_examples.*.example.*.hiragana' => 'nullable|string|max:750',
            'sentence_examples.*.example.*.romaji' => 'nullable|string|max:750',
            'sentence_examples.*.example.*.pinyin' => 'nullable|string|max:750',

            'dialogue_examples' => 'required|array|min:2',
            'dialogue_examples.*.speaker' => 'required|in:A,B',
            'dialogue_examples.*.line' => 'required:string|max:500',
            'dialogue_examples.*.translation' => 'required|array|min:3',
            'dialogue_examples.*.translation.*.hiragana' => 'nullable|string|max:750',
            'dialogue_examples.*.translation.*.romaji' => 'nullable|string|max:750',
            'dialogue_examples.*.translation.*.pinyin' => 'nullable|string|max:750',

            'additional_notes' => 'nullable|string|max:1000',
        ]);

        $entry = VocabularyEntry::create($validated);
        return response()->json($entry, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $entry = VocabularyEntry::with(['sentenceExamples', 'dialogueExamples'])->findOrFail($id);
        return response()->json($entry, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $entry = VocabularyEntry::findOrFail($id);
        return response()->json($entry, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        VocabularyEntry::findOrFail($id)->delete();
        return response()->json(['message', `Vocabulary entry with id $id successfully deleted`], 204);
    }

    public function voteExample(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|string',
            'example_id' => 'required|integer',
            'is_upvote' => 'required|boolean',
            'example_type' => ['required', Rule::in('sentence', 'dialogue')],
        ]);

        $example = $validated['example_type'] === 'sentence' 
            ? SentenceExample::findOrFail($validated['example_id'])
            : DialogueExample::findOrFail($validated['example_id']);
        
        $voteType = $validated['is_upvote'] === true ? 'upvote' : 'downvote';
        $example->addVote($validated['user_id'], $voteType);

        return response()->json(['message' => 'Vote recorded', 'example' => $example], 200);
    }
}
