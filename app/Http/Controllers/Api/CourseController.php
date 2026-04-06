<?php

namespace App\Http\Controllers\Api;

use App\Models\Course;
use App\Queries\Learning\CourseDetailsQuery;
use Illuminate\Http\JsonResponse;

class CourseController extends BaseController
{
    public function __construct(
        protected CourseDetailsQuery $detailsQuery
    ) {}

    /**
     * Display a specific course with its public or enrolled details.
     */
    public function show(int $courseId): JsonResponse
    {
        $userId = request()->user('sanctum')?->id; // Can be null if public (depending on route auth)

        try {
            $details = $this->detailsQuery->execute($courseId, $userId);
            return $this->sendSuccess(['course' => $details]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendError('Course not found.', 404);
        }
    }
}
