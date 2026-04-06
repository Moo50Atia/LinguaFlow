<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    /**
     * Create a new notification for a user.
     *
     * @param int $userId
     * @param string $type
     * @param string $title
     * @param string|null $body
     * @param array $data
     * @param string|null $icon
     * @return Notification
     */
    public function create(int $userId, string $type, string $title, ?string $body = null, array $data = [], ?string $icon = null): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'body'    => $body,
            'data'    => $data,
            'icon'    => $icon,
        ]);
    }

    /**
     * Mark a specific notification as read.
     *
     * @param Notification $notification
     * @return void
     */
    public function markRead(Notification $notification): void
    {
        $notification->update(['read_at' => now()]);
    }

    /**
     * Mark all unread notifications for a user as read.
     *
     * @param int $userId
     * @return void
     */
    public function markAllRead(int $userId): void
    {
        Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
