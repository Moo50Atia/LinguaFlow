<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\ChatMember;
use App\Models\Message;
use App\Models\Moment;
use App\Models\MomentComment;
use App\Models\MomentCorrection;
use App\Models\MomentLike;
use App\Models\User;
use Illuminate\Database\Seeder;

class SocialSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        // 1. Seed Moments (Social Feed)
        Moment::factory(15)->create()->each(function ($moment) use ($users) {
            // Add some corrections to moments
            MomentCorrection::factory(fake()->numberBetween(0, 2))->create([
                'moment_id' => $moment->id,
                'user_id' => $users->random()->id,
            ]);

            // Add some likes
            $likers = $users->random(fake()->numberBetween(2, 10));
            foreach ($likers as $user) {
                MomentLike::create([
                    'moment_id' => $moment->id,
                    'user_id' => $user->id,
                ]);
            }

            // Add some comments
            MomentComment::factory(fake()->numberBetween(1, 5))->create([
                'moment_id' => $moment->id,
                'user_id' => $users->random()->id,
            ]);
        });

        // 2. Seed Chats
        // Create 10 direct chats between random users
        for ($i = 0; $i < 10; $i++) {
            $pair = $users->random(2);
            $chat = Chat::create(['type' => 'direct']);
            
            ChatMember::create(['chat_id' => $chat->id, 'user_id' => $pair[0]->id, 'role' => 'member']);
            ChatMember::create(['chat_id' => $chat->id, 'user_id' => $pair[1]->id, 'role' => 'member']);

            Message::factory(10)->create([
                'chat_id' => $chat->id,
                'sender_id' => fn() => fake()->randomElement($pair)->id,
            ]);
        }

        // Create 2 group chats
        for ($i = 0; $i < 2; $i++) {
            $chat = Chat::create(['type' => 'group', 'name' => fake()->word() . ' Study Group']);
            $members = $users->random(5);
            
            foreach ($members as $index => $user) {
                ChatMember::create([
                    'chat_id' => $chat->id,
                    'user_id' => $user->id,
                    'role' => $index === 0 ? 'admin' : 'member',
                ]);
            }

            Message::factory(20)->create([
                'chat_id' => $chat->id,
                'sender_id' => fn() => $members->random()->id,
            ]);
        }
    }
}
