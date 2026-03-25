<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserInterest;
use App\Models\UserLanguage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create a Super Admin
        $admin = User::factory()->admin()->create([
            'name' => 'System Administrator',
            'email' => 'admin@linguaflow.com',
            'password' => Hash::make('password123'),
        ]);

        // 2. Create specific Instructors (matching React mockData)
        $instructorsData = [
            ['name' => 'Sam Jenkins', 'email' => 'sam@linguaflow.com'],
            ['name' => 'Carlos Mateo', 'email' => 'carlos@linguaflow.com'],
            ['name' => 'Wei Lin', 'email' => 'wei@linguaflow.com'],
        ];

        foreach ($instructorsData as $data) {
            $user = User::factory()->instructor()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password123'),
            ]);

            $this->seedUserProfile($user);
        }

        // 3. Create a Demo Student
        $student = User::factory()->create([
            'name' => 'Demo Student',
            'email' => 'student@linguaflow.com',
            'password' => Hash::make('password123'),
        ]);
        $this->seedUserProfile($student);

        // 4. Create random students
        User::factory(20)->create()->each(function ($user) {
            $this->seedUserProfile($user);
        });
    }

    private function seedUserProfile(User $user)
    {
        // Give each user some languages
        UserLanguage::factory()->count(fake()->numberBetween(1, 3))->create([
            'user_id' => $user->id,
        ]);

        // Give each user some interests
        UserInterest::factory()->count(fake()->numberBetween(2, 5))->create([
            'user_id' => $user->id,
        ]);
    }
}
