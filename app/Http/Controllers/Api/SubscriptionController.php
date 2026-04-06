<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Profile\SubscribeRequest;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends BaseController
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    ) {}

    /**
     * Handle pushing through a VIP subscription via Mock handler.
     */
    public function checkout(SubscribeRequest $request): JsonResponse
    {
        $this->subscriptionService->checkout($request->user(), $request->validated());

        return $this->sendSuccess([
            'is_vip' => true
        ], 'Checkout successful. You are now a VIP user.');
    }
}
