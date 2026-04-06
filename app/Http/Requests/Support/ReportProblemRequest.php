<?php

namespace App\Http\Requests\Support;

use Illuminate\Foundation\Http\FormRequest;

class ReportProblemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'        => 'required|in:bug,payment,harassment,other',
            'description' => 'required|string|max:2000',
            'attachment'  => 'nullable|image|max:5120', // 5MB max screenshot
        ];
    }
}
