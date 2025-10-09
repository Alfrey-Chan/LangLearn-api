<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVocabularySetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'language_code' => 'required|string|exists:languages,code',
            'title' => 'required|string|max:255',
            'tags' => 'array',
            'tags.*' => 'string|max:50',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'description' => 'required|string|max:1000',
            'image_url' => 'nullable|url',
        ];
    }

    /**
    * Get custom error messages for validation rules.
    */
    public function messages(): array
    {
        return [
            'title.required' => 'Vocabulary set title is required.',
            'difficulty.in' => 'Difficulty must be beginner, intermediate, or advanced.',
            'language_code.exists' => 'The selected language is invalid.',
        ];
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {   
        $this->merge([
            'difficulty' => strtolower($this->difficulty), 
        ]);
    }
}
