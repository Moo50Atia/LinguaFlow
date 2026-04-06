<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Str;

class HandleGoogleOAuthAction
{
    /**
     * Handle the external OAuth flow with find-or-create branching.
     *
     * @param mixed $googleUser
     * @return array
     */
    public function execute($googleUser): array
    {
        // Find existing user by Google ID or Email
        $user = User::where('google_id', $googleUser->id)
                    ->orWhere('email', $googleUser->email)
                    ->first();

        if ($user) {
            // Update Google ID if matched by email
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->id]);
            }
        } else {
            // Create a new user if none match
            $user = User::create([
                'name'      => $googleUser->name,
                'email'     => $googleUser->email,
                'google_id' => $googleUser->id,
                'password'  => bcrypt(Str::random(16)), // Fallback password
                'avatar'    => $googleUser->avatar,
                'role'      => 'student', // Default role
            ]);
        }

        // Set online status
        $user->update([
            'is_online'    => true,
            'last_seen_at' => now(),
        ]);

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }
}
