<?php

namespace App\Services;

use App\Models\User;

class SubscriptionService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Handle mock subscription checkout.
     */
    public function checkout(User $user, array $data): void
    {
        // 1. Simulating payment processor success via Mock
        
        // 2. Upgrade user locally
        $user->update(['is_vip' => true]);

        // 3. Dispatch system notification
        $this->notificationService->create(
            $user->id,
            'subscription_activated',
            'Welcome to VIP!',
            "Your {$data['plan_id']} subscription has been successfully processed."
        );
    }
}
