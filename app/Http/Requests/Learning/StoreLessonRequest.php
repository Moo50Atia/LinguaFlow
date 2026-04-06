<?php

namespace App\Http\Requests\Learning;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorized via LessonPolicy in controller
    }

    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'order'       => 'required|integer|min:1',
            'description' => 'nullable|string',
            'notes'       => 'nullable|string',
            'duration'    => 'nullable|string|max:50',
            'image'       => 'nullable|image|max:5120',
        ];
    }
}
