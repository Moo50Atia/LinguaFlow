<?php

namespace App\Http\Requests\Learning;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'instructor' || $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'title'        => 'sometimes|required|string|max:255',
            'level'        => 'sometimes|required|string|in:A1,A2,B1,B2,C1,C2,All Levels',
            'language'     => 'sometimes|required|string',
            'price'        => 'sometimes|required|numeric|min:0',
            'category'     => 'sometimes|required|string',
            'description'  => 'sometimes|required|string',
            'is_published' => 'sometimes|boolean',
            'image'        => 'nullable|image|max:5120',
        ];
    }
}
