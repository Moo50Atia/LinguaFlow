<?php

namespace App\Http\Controllers\Api;

use App\Actions\Community\SendMessageAction;
use App\Http\Requests\Community\StoreMessageRequest;
use App\Queries\Community\ConversationThreadQuery;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends BaseController
{
    /**
     * Get recent unique conversations (inbox view).
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // Grouping logic to get latest message from each unique counterpart.
        // Requires a RAW subquery or specific Eloquent orchestration.
        // simplified approach for prototype:
        $latestMessages = Message::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with(['sender', 'receiver'])
            ->latest()
            ->get()
            ->unique(function ($item) use ($userId) {
                // Group by the counterpart's ID
                return $item->sender_id === $userId ? $item->receiver_id : $item->sender_id;
            })
            ->values();

        return $this->sendSuccess([
            'conversations' => MessageResource::collection($latestMessages)
        ]);
    }

    /**
     * Retrieve the chat history with a specific user.
     */
    public function show(int $userId, Request $request, ConversationThreadQuery $query): JsonResponse
    {
        $messages = $query->execute($request->user()->id, $userId);

        return $this->sendSuccess([
            'thread' => MessageResource::collection($messages)->response()->getData(true)
        ]);
    }

    /**
     * Send a new message.
     */
    public function store(StoreMessageRequest $request, SendMessageAction $action): JsonResponse
    {
        $message = $action->execute(
            $request->user(),
            $request->validated('receiver_id'),
            $request->validated('content')
        );

        return $this->sendCreated([
            // Return raw resource so the client can append it immediately to thread
            'message' => clone new MessageResource($message->load('sender', 'receiver'))
        ]);
    }
}
