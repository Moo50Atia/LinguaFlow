<?php

namespace App\Http\Controllers\Api;

use App\Actions\Learning\EnrollStudentAction;
use App\Http\Requests\Learning\StoreEnrollmentRequest;
use App\Http\Resources\EnrollmentResource;
use Illuminate\Http\JsonResponse;

class EnrollmentController extends BaseController
{
    public function __construct(
        protected EnrollStudentAction $enrollAction
    ) {}

    /**
     * Enroll the authenticated student in a course.
     */
    public function store(StoreEnrollmentRequest $request): JsonResponse
    {
        $enrollment = $this->enrollAction->execute(
            $request->user(),
            $request->validated('course_id')
        );

        return $this->sendCreated([
            'enrollment' => clone new EnrollmentResource($enrollment)
        ], 'Successfully enrolled in course.');
    }
}
