<?php

namespace App\Http\Controllers\Api;

use App\Models\Course;
use App\Models\Lesson;
use App\Http\Requests\Learning\StoreLessonRequest;
use App\Http\Resources\LessonResource;
use App\Services\LessonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class LessonController extends BaseController
{
    use AuthorizesRequests;

    public function __construct(
        protected LessonService $lessonService
    ) {}

    public function store(StoreLessonRequest $request, Course $course): JsonResponse
    {
        $this->authorize('create', [Lesson::class, $course]);

        $lesson = $this->lessonService->create($course->id, $request->validated());

        return $this->sendCreated([
            'lesson' => new LessonResource($lesson)
        ]);
    }

    public function update(StoreLessonRequest $request, Lesson $lesson): JsonResponse
    {
        $this->authorize('update', $lesson);

        $lesson = $this->lessonService->update($lesson, $request->validated());

        return $this->sendSuccess([
            'lesson' => new LessonResource($lesson)
        ]);
    }

    public function destroy(Lesson $lesson): JsonResponse
    {
        $this->authorize('delete', $lesson);

        $this->lessonService->delete($lesson);

        return $this->sendDeleted();
    }
}
