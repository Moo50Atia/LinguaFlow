<?php

namespace App\Http\Requests\Learning;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuizQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lesson_id'      => 'nullable|exists:lessons,id',
            'course_id'      => 'nullable|exists:courses,id',
            'type'           => 'required|in:lesson_quiz,final_assessment',
            'question'       => 'required|string',
            'options'        => 'required|array|min:2',
            'options.*'      => 'required|string',
            'correct_answer' => 'required|integer|min:0',
            'order'          => 'required|integer|min:1',
        ];
    }
}
