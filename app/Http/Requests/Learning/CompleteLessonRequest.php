<?php

namespace App\Http\Requests\Learning;

use Illuminate\Foundation\Http\FormRequest;

class CompleteLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Lesson view policy handles access
    }

    public function rules(): array
    {
        return [
            'score' => 'nullable|integer|min:0|max:100',
        ];
    }
}
