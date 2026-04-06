<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Pagination\LengthAwarePaginator;

class CourseService
{
    public function __construct(
        protected FileUploadService $fileUploadService
    ) {}

    public function list(int $instructorId): LengthAwarePaginator
    {
        return Course::where('instructor_id', $instructorId)
            ->withCount(['lessons', 'enrollments'])
            ->latest()
            ->paginate(15);
    }

    public function create(int $instructorId, array $data): Course
    {
        $imagePath = null;
        if (isset($data['image'])) {
            $imagePath = $this->fileUploadService->store($data['image'], 'courses');
        }

        return Course::create([
            'instructor_id' => $instructorId,
            'title'         => $data['title'],
            'level'         => $data['level'],
            'language'      => $data['language'],
            'price'         => $data['price'],
            'category'      => $data['category'],
            'description'   => $data['description'],
            'image'         => $imagePath,
            'is_published'  => false,
        ]);
    }

    public function update(Course $course, array $data): Course
    {
        // Handle image upload if present
        if (isset($data['image'])) {
            $this->fileUploadService->delete($course->image);
            $data['image'] = $this->fileUploadService->store($data['image'], 'courses');
        }

        $course->update($data);
        return $course;
    }

    public function delete(Course $course): void
    {
        $this->fileUploadService->delete($course->image);
        $course->delete();
    }

    public function publish(Course $course): void
    {
        $course->update(['is_published' => true]);
    }
}
