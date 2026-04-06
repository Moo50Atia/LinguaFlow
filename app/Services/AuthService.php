<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user and generate a token.
     *
     * @param array $data
     * @return array
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'gender'   => $data['gender'] ?? null,
            'role'     => 'student', // Default role
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    /**
     * Authenticate a user and generate a token.
     *
     * @param array $credentials
     * @return array
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();
        
        $user->update([
            'is_online'    => true,
            'last_seen_at' => now(),
        ]);

        // Revoke old tokens for security (optional behavior)
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout a user by revoking their current token.
     *
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        $user->update([
            'is_online' => false,
            'last_seen_at' => now(),
        ]);

        $user->currentAccessToken()->delete();
    }
}
