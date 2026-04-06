<?php

namespace App\Http\Controllers\Api;

use App\Actions\Auth\CompleteOnboardingAction;
use App\Actions\Auth\HandleGoogleOAuthAction;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\OnboardingRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends BaseController
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Register a new user.
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return $this->sendCreated([
            'user'  => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Registration successful.');
    }

    /**
     * Authenticate an existing user.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return $this->sendSuccess([
            'user'  => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Login successful.');
    }

    /**
     * Handle the complete onboarding multi-step wizard submission.
     *
     * @param OnboardingRequest $request
     * @param CompleteOnboardingAction $action
     * @return JsonResponse
     */
    public function onboarding(OnboardingRequest $request, CompleteOnboardingAction $action): JsonResponse
    {
        $user = $action->execute($request->user(), $request->validated());

        return $this->sendSuccess([
            'user' => new UserResource($user),
        ], 'Onboarding completed successfully.');
    }

    /**
     * Logout the user by revoking token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->sendSuccess([], 'Logged out successfully.');
    }

    /**
     * Redirect to Google OAuth.
     *
     * @return JsonResponse
     */
    public function googleRedirect(): JsonResponse
    {
        if (!class_exists(\Laravel\Socialite\Facades\Socialite::class)) {
            return $this->sendError('Socialite is not installed', 501);
        }

        $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();

        return $this->sendSuccess(['url' => $url], 'Redirect URL generated');
    }

    /**
     * Handle the callback from Google OAuth.
     *
     * @param HandleGoogleOAuthAction $action
     * @return JsonResponse
     */
    public function googleCallback(HandleGoogleOAuthAction $action): JsonResponse
    {
        if (!class_exists(\Laravel\Socialite\Facades\Socialite::class)) {
            return $this->sendError('Socialite is not installed', 501);
        }

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $result = $action->execute($googleUser);

            return $this->sendSuccess([
                'user'  => new UserResource($result['user']),
                'token' => $result['token'],
            ], 'Google authentication successful.');
            
        } catch (\Exception $e) {
            return $this->sendError('Authentication failed: ' . $e->getMessage());
        }
    }
}
