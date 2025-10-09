<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuizAnswersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {   
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'questions' => 'required|array|min:1',
            'questions.*.id' => 'required|integer',
            'questions.*.quiz_id' => 'required|integer|exists:quizzes,id',
            'questions.*.type' => 'required|in:multiple_choice,translation,sentence_creation,fill_blank,word_rearrangement',
            'questions.*.user_answer' => 'required|string',
            'questions.*.points' => 'required|integer|min:1|max:3',
            'questions.*.target_word' => 'required|string',

            // Conditional validation based on the question type
            'questions.*.correct_answer' => 'nullable|string',
            'questions.*.word_bank' => 'nullable|array',
            'questions.*.options' => 'nullable|array',
            'questions.*.acceptable_answers' => 'nullable|array', 
            'questions.*.requires_feedback' => 'nullable|boolean', 
        ];
    }

    /**
    * Get custom error messages for validation rules.
    */
    public function messages(): array
    {
        return [
            'questions.required' => 'Quiz questions are required.',
            'questions.min' => 'At least one question must be answered.',
            'questions.*.quiz_id.exists' => 'One or more questions reference an invalid quiz.',
            'questions.*.type.in' => 'Invalid question type provided.',
            'questions.*.user_answer.required' => 'All questions must have an answer.',
            'questions.*.points.min' => 'Question points must be at least 1.',
        ];
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {   
        //
    }
}
