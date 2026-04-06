<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => 'sometimes|required|string|max:255',
            'email'           => 'sometimes|required|email|unique:users,email,' . $this->user()->id,
            'bio'             => 'nullable|string|max:1000',
            'location'        => 'nullable|string|max:255',
            'gender'          => 'sometimes|in:male,female,other,prefer_not_to_say',
            'native_language' => 'sometimes|string|max:100',
            'cefr_level'      => 'nullable|in:A1,A2,B1,B2,C1,C2',
            'avatar'          => 'nullable|image|max:5120',
        ];
    }
}
