<?php

namespace App\Queries\Community;

use App\Models\Message;
use Illuminate\Pagination\LengthAwarePaginator;

class ConversationThreadQuery
{
    /**
     * Get a chronologically reversed paginated list of messages between two users.
     */
    public function execute(int $userId1, int $userId2): LengthAwarePaginator
    {
        // Mark all unread messages sent by User 2 to User 1 as read when User 1 opens the thread
        Message::where('sender_id', $userId2)
               ->where('receiver_id', $userId1)
               ->where('is_read', false)
               ->update(['is_read' => true]);

        return Message::where(function ($query) use ($userId1, $userId2) {
            $query->where('sender_id', $userId1)->where('receiver_id', $userId2);
        })->orWhere(function ($query) use ($userId1, $userId2) {
            $query->where('sender_id', $userId2)->where('receiver_id', $userId1);
        })
        ->with(['sender', 'receiver'])
        ->latest() // Orders by latest strictly for pagination
        ->paginate(30);
    }
}
