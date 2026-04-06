<?php

namespace App\Queries\Community;

use App\Models\Moment;
use Illuminate\Pagination\LengthAwarePaginator;

class MomentsFeedQuery
{
    /**
     * Get the community moments feed with loaded relations and optional filters.
     *
     * @param int $currentUserId
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function execute(int $currentUserId, array $filters = []): LengthAwarePaginator
    {
        $query = Moment::with([
            'user' => function($q) {
                $q->select('id', 'name', 'avatar', 'native_language');
            },
            'corrections.corrector' => function($q) {
                $q->select('id', 'name', 'avatar');
            }
        ]);

        if (!empty($filters['filter'])) {
            $query->where('category', $filters['filter']);
        }

        // Add dynamically calculated boolean for 'is_liked' to see if current user liked it
        $query->withExists(['likes as is_liked' => function ($q) use ($currentUserId) {
            $q->where('user_id', $currentUserId);
        }]);

        return $query->latest()->paginate(15);
    }
}
