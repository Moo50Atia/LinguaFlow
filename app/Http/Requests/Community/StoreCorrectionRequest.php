<?php

namespace App\Http\Requests\Community;

use Illuminate\Foundation\Http\FormRequest;

class StoreCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'corrected_text' => 'required|string|max:5000',
            'notes'          => 'nullable|string|max:1000',
        ];
    }
}
