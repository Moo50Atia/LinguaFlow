<?php

namespace App\Queries\Instructor;

use App\Models\Instructor;
use Illuminate\Pagination\LengthAwarePaginator;

class InstructorCatalogQuery
{
    /**
     * Get a paginated list of instructors with filters applied.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function execute(array $filters = []): LengthAwarePaginator
    {
        $query = Instructor::with(['user', 'courses' => function($q) {
            $q->where('is_published', true);
        }]);

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['gender'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('gender', $filters['gender']);
            });
        }
        
        if (!empty($filters['search'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->paginate(15);
    }
}
