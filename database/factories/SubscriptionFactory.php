<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        $plan = fake()->randomElement(['pro_learner', 'vip_elite', 'enterprise']);
        $prices = [
            'pro_learner' => 19.99,
            'vip_elite' => 49.99,
            'enterprise' => 299.00,
        ];

        return [
            'user_id' => User::factory(),
            'plan' => $plan,
            'price' => $prices[$plan],
            'billing_cycle' => fake()->randomElement(['monthly', 'yearly']),
            'status' => fake()->randomElement(['active', 'trial']),
            'stripe_subscription_id' => 'sub_' . fake()->regexify('[A-Za-z0-9]{14}'),
            'stripe_customer_id' => 'cus_' . fake()->regexify('[A-Za-z0-9]{14}'),
            'trial_ends_at' => now()->addDays(14),
            'starts_at' => now()->subDays(30),
            'ends_at' => now()->addMonths(1),
            'cancelled_at' => null,
        ];
    }
}
