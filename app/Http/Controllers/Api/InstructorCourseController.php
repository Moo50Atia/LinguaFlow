<?php

namespace App\Http\Controllers\Api;

use App\Models\Course;
use App\Http\Requests\Learning\StoreCourseRequest;
use App\Http\Requests\Learning\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Services\CourseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class InstructorCourseController extends BaseController
{
    use AuthorizesRequests;

    public function __construct(
        protected CourseService $courseService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $instructorId = $request->user()->instructor->id;
        $courses = $this->courseService->list($instructorId);

        return $this->sendSuccess([
            'courses' => CourseResource::collection($courses)->response()->getData(true)
        ]);
    }

    public function store(StoreCourseRequest $request): JsonResponse
    {
        $course = $this->courseService->create(
            $request->user()->instructor->id,
            $request->validated()
        );

        return $this->sendCreated([
            'course' => new CourseResource($course)
        ]);
    }

    public function show(Course $course): JsonResponse
    {
        $this->authorize('update', $course); // Instructor level view

        $course->load(['lessons', 'instructor.user']);
        
        return $this->sendSuccess([
            'course' => new CourseResource($course)
        ]);
    }

    public function update(UpdateCourseRequest $request, Course $course): JsonResponse
    {
        $this->authorize('update', $course);

        $course = $this->courseService->update($course, $request->validated());

        return $this->sendSuccess([
            'course' => new CourseResource($course)
        ]);
    }

    public function destroy(Course $course): JsonResponse
    {
        $this->authorize('delete', $course);

        $this->courseService->delete($course);

        return $this->sendDeleted();
    }
}
