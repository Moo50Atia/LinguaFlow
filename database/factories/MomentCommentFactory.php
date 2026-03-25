<?php

namespace Database\Factories;

use App\Models\Moment;
use App\Models\MomentComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MomentCommentFactory extends Factory
{
    protected $model = MomentComment::class;

    public function definition(): array
    {
        return [
            'moment_id' => Moment::factory(),
            'user_id' => User::factory(),
            'body' => fake()->sentence(),
        ];
    }
}
