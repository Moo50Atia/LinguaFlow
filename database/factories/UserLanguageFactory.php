<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserLanguage;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserLanguageFactory extends Factory
{
    protected $model = UserLanguage::class;

    public function definition(): array
    {
        $languages = [
            ['language' => 'English', 'flag' => '🇬🇧'],
            ['language' => 'Arabic', 'flag' => '🇸🇦'],
            ['language' => 'Spanish', 'flag' => '🇪🇸'],
            ['language' => 'French', 'flag' => '🇫🇷'],
            ['language' => 'German', 'flag' => '🇩🇪'],
            ['language' => 'Chinese', 'flag' => '🇨🇳'],
            ['language' => 'Japanese', 'flag' => '🇯🇵'],
        ];

        $lang = fake()->randomElement($languages);

        return [
            'user_id' => User::factory(),
            'language' => $lang['language'],
            'flag' => $lang['flag'],
            'level' => fake()->randomElement(['A1.1', 'A1.2', 'A2', 'B1.1', 'B1.2', 'B2', 'C1', 'C2', 'Native']),
            'is_native' => fake()->boolean(20),
        ];
    }
}
