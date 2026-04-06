<?php

namespace App\Http\Requests\Community;

use Illuminate\Foundation\Http\FormRequest;

class StoreMomentRequest extends FormRequest
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
            'content'  => 'required|string|max:5000',
            'category' => 'required|in:General,Grammar,Vocabulary,Culture',
            'images'   => 'nullable|array|max:4',
            'images.*' => 'image|max:5120', // 5MB per image max
        ];
    }
}
