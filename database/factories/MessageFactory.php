<?php

namespace Database\Factories;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        $isCorrection = fake()->boolean(20);

        return [
            'chat_id' => Chat::factory(),
            'sender_id' => User::factory(),
            'text' => $isCorrection ? null : fake()->sentence(),
            'is_correction' => $isCorrection,
            'original_text' => $isCorrection ? fake()->sentence() : null,
            'corrected_text' => $isCorrection ? fake()->sentence() : null,
        ];
    }
}
