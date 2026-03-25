<?php

namespace Database\Factories;

use App\Models\Moment;
use App\Models\MomentLike;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MomentLikeFactory extends Factory
{
    protected $model = MomentLike::class;

    public function definition(): array
    {
        return [
            'moment_id' => Moment::factory(),
            'user_id' => User::factory(),
        ];
    }
}
