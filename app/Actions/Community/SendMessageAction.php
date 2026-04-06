<?php

namespace App\Actions\Community;

use App\Models\Message;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Validation\ValidationException;

class SendMessageAction
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function execute(User $sender, int $receiverId, string $content): Message
    {
        if ($sender->id === $receiverId) {
            throw ValidationException::withMessages([
                'receiver_id' => ['You cannot message yourself.']
            ]);
        }

        // Potential Extension: Validates if the sender is actually friends with the receiver
        // Or if it's an Instructor to Student connection via Enrollment.
        // For now we allow open messaging for the prototype.
        
        $message = Message::create([
            'sender_id'   => $sender->id,
            'receiver_id' => $receiverId,
            'content'     => $content,
            'is_read'     => false,
        ]);

        // Push real-time notification (or socket broadcast)
        // If broadcasting via web sockets, we would dispatch an Event here.
        // e.g., event(new MessageSent($message));
        
        $this->notificationService->create(
            $receiverId,
            'new_message',
            "New message from {$sender->name}",
            substr($content, 0, 50) . '...' // Snippet
        );

        return $message;
    }
}
