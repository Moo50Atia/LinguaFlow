<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class OnboardingRequest extends FormRequest
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
            'native_language'            => 'required|string',
            'learning_languages'         => 'required|array|min:1',
            'learning_languages.*.name'  => 'required|string',
            'learning_languages.*.level' => 'required|in:A1,A2,B1,B2,C1,C2,Native',
            'interests'                  => 'required|array|min:1',
            'interests.*'                => 'required|string',
            'quiz_score'                 => 'nullable|integer|min:0|max:100',
        ];
    }
}
