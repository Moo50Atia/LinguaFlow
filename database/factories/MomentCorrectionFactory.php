<?php

namespace Database\Factories;

use App\Models\Moment;
use App\Models\MomentCorrection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MomentCorrectionFactory extends Factory
{
    protected $model = MomentCorrection::class;

    public function definition(): array
    {
        return [
            'moment_id' => Moment::factory(),
            'user_id' => User::factory(),
            'original_text' => fake()->sentence(),
            'corrected_text' => fake()->sentence(),
        ];
    }
}
