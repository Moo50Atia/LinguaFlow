<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // For a real production app, this would validate payment tokens, plan IDs, etc.
        // For our prototype, we're mocking the transaction boundary.
        return [
            'plan_id'        => 'required|in:pro_monthly,pro_yearly',
            'payment_method' => 'required|in:mock_card,mock_paypal',
        ];
    }
}
