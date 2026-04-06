<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    /**
     * Determine if the user can interact with the message.
     */
    public function interact(User $user, Message $message): bool
    {
        return $message->sender_id === $user->id || $message->receiver_id === $user->id || $user->role === 'admin';
    }
}
