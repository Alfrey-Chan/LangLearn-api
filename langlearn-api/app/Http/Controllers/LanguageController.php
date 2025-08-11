<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Language::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:5|unique:languages',
            'name' => 'required|string',
            'native_name' => 'required|string',
            'is_active' => 'nullable|boolean'
        ]);

        $language = Language::create($validated);

        return response()->json($language, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Language::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $language = Language::findOrFail($id);

        $validated = $request->validate([
          'code' => 'string|max:5|unique:languages',
          'name' => 'string|max:50|unique:languages',
          'native_name' => 'string|max:50',
          'is_active' => 'nullable|boolean'
        ]);

        $language->update($validated);
        return $language;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $language = Language::findOrFail($id);
        $language->delete();
        return response()->json(['message' => `Language with id $id successfully deleted`], 204);
    }
}
