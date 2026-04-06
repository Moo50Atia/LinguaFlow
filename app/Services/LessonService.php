<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\Course;

class LessonService
{
    public function __construct(
        protected FileUploadService $fileUploadService
    ) {}

    public function create(int $courseId, array $data): Lesson
    {
        $imagePath = null;
        if (isset($data['image'])) {
            $imagePath = $this->fileUploadService->store($data['image'], 'lessons');
        }

        return Lesson::create([
            'course_id'   => $courseId,
            'title'       => $data['title'],
            'order'       => $data['order'],
            'description' => $data['description'] ?? null,
            'notes'       => $data['notes'] ?? null,
            'duration'    => $data['duration'] ?? null,
            'image'       => $imagePath,
            'status'      => 'active',
        ]);
    }

    public function update(Lesson $lesson, array $data): Lesson
    {
        if (isset($data['image'])) {
            $this->fileUploadService->delete($lesson->image);
            $data['image'] = $this->fileUploadService->store($data['image'], 'lessons');
        }

        $lesson->update($data);
        return $lesson;
    }

    public function delete(Lesson $lesson): void
    {
        $this->fileUploadService->delete($lesson->image);
        $lesson->delete();
    }

    public function reorder(int $courseId, array $orderMap): void
    {
        foreach ($orderMap as $lessonId => $newOrder) {
            Lesson::where('id', $lessonId)
                  ->where('course_id', $courseId)
                  ->update(['order' => $newOrder]);
        }
    }
}
