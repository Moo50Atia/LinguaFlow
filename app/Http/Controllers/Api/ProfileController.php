<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends BaseController
{
    public function __construct(
        protected ProfileService $profileService
    ) {}

    /**
     * Get authenticated user profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load(['learningLanguages', 'interests']);

        return $this->sendSuccess([
            'user' => new UserResource($user)
        ]);
    }

    /**
     * Update authenticated user profile.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->profileService->updateBaseProfile($request->user(), $request->validated());

        return $this->sendSuccess([
            'user' => new UserResource($user->load(['learningLanguages', 'interests']))
        ], 'Profile updated successfully.');
    }
}
