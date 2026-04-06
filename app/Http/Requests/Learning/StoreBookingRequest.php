<?php

namespace App\Http\Requests\Learning;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
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
            'instructor_id'      => 'required|exists:instructors,id',
            'instructor_slot_id' => 'required|exists:instructor_slots,id',
            'booking_type'       => 'required|in:complete-course,specific-session',
            'course_style'       => 'nullable|in:private,group',
            'date'               => 'required|date|after_or_equal:today',
            'time'               => 'required|date_format:H:i',
            'notes'              => 'nullable|string|max:1000',
        ];
    }
}
