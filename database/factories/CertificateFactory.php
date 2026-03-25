<?php

namespace Database\Factories;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CertificateFactory extends Factory
{
    protected $model = Certificate::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'title' => 'Professional Translation Certificate',
            'certificate_number' => 'LF-' . strtoupper(Str::random(8)),
            'level' => fake()->randomElement(['B2', 'C1', 'C2']),
            'category' => fake()->randomElement(['Legal', 'Medical', 'Business']),
            'file_path' => 'certificates/' . fake()->uuid() . '.pdf',
            'issued_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
