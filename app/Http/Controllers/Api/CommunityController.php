<?php

namespace App\Http\Controllers\Api;

use App\Actions\Community\DiscoverLanguagePartnersAction;
use App\Http\Requests\Community\StoreFriendRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityController extends BaseController
{
    /**
     * Discover potential language partners based on matching algorithm.
     */
    public function discover(Request $request, DiscoverLanguagePartnersAction $action): JsonResponse
    {
        $partners = $action->execute($request->user(), $request->all());

        // We can use a mutated UserResource or raw array mapped
        return $this->sendSuccess([
            'partners' => $partners
        ]);
    }

    /**
     * Send a friend/connection request (Stub for Sprint 5 logic).
     */
    public function sendFriendRequest(StoreFriendRequest $request): JsonResponse
    {
        $targetUserId = $request->validated('user_id');

        // Logic handled simply: e.g. saving to a 'connections' table
        \DB::table('friend_requests')->insertOrIgnore([
            'sender_id'   => $request->user()->id,
            'receiver_id' => $targetUserId,
            'created_at'  => now(),
        ]);

        return $this->sendSuccess([], 'Connection request sent.');
    }
}
