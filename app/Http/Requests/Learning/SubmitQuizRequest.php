<?php

namespace App\Http\Requests\Learning;

use Illuminate\Foundation\Http\FormRequest;

class SubmitQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lesson_id'                 => 'required|exists:lessons,id',
            'answers'                   => 'required|array',
            'answers.*.question_id'     => 'required|exists:quiz_questions,id',
            'answers.*.selected_option' => 'required',
        ];
    }
}
