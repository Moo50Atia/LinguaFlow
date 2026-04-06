<?php

namespace App\Http\Requests\Learning;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'instructor' || $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'level'       => 'required|string|in:A1,A2,B1,B2,C1,C2,All Levels',
            'language'    => 'required|string',
            'price'       => 'required|numeric|min:0',
            'category'    => 'required|string',
            'description' => 'required|string',
            'image'       => 'nullable|image|max:5120',
        ];
    }
}
