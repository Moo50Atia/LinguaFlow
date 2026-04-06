<?php

namespace App\Http\Controllers\Api;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonMaterial;
use App\Http\Requests\Learning\StoreMaterialRequest;
use App\Services\LessonMaterialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MaterialController extends BaseController
{
    use AuthorizesRequests;

    public function __construct(
        protected LessonMaterialService $materialService
    ) {}

    public function store(StoreMaterialRequest $request, Lesson $lesson): JsonResponse
    {
        $this->authorize('update', $lesson); // Must own the lesson

        $material = $this->materialService->upload($lesson->id, $request->file('file'), $request->validated('name'));

        return $this->sendCreated([
            'material' => $material
        ]);
    }

    public function destroy(LessonMaterial $material): JsonResponse
    {
        $this->authorize('update', $material->lesson);

        $this->materialService->delete($material);

        return $this->sendDeleted();
    }
}
