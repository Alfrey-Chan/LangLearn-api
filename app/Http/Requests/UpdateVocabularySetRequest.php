<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVocabularySetRequest extends FormRequest
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
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'difficulty' => 'sometimes|in:beginner,intermediate,advanced',
            'is_active' => 'sometimes|boolean',
            'language_code' => 'sometimes|string|exists:languages,code',
            'category' => 'sometimes|string|max:100',
            'image_url' => 'sometimes|url',
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
        // TODO:: after admin dashboard is implemented
        // if ($this->has('difficulty')) {
        //     $this->merge([
        //         'difficulty' => strtolower($this->difficulty),
        //     ]);
        // }
    }
}
