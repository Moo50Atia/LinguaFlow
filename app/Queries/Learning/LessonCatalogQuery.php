<?php

namespace App\Queries\Learning;

use App\Models\Lesson;
use Illuminate\Pagination\LengthAwarePaginator;

class LessonCatalogQuery
{
    /**
     * Fetch a paginated catalog of all available lessons across courses for discovery view.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function execute(array $filters = []): LengthAwarePaginator
    {
        $query = Lesson::where('status', 'active')
            ->whereHas('course', function ($q) {
                $q->where('is_published', true);
            })
            ->with(['course' => function($q) {
                $q->select('id', 'title', 'level', 'category', 'instructor_id')->with('instructor.user');
            }]);

        if (!empty($filters['level'])) {
            $query->whereHas('course', function ($q) use ($filters) {
                $q->where('level', $filters['level']);
            });
        }

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        return $query->latest()->paginate(15);
    }
}
