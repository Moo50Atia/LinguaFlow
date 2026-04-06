<?php

namespace App\Services;

use App\Models\LessonMaterial;

class LessonMaterialService
{
    public function __construct(
        protected FileUploadService $fileUploadService
    ) {}

    public function upload(int $lessonId, \Illuminate\Http\UploadedFile $file, string $name): LessonMaterial
    {
        $path = $this->fileUploadService->store($file, 'materials');
        
        // Basic type inference
        $type = 'document';
        if (str_starts_with($file->getMimeType(), 'video')) {
            $type = 'video';
        } elseif (str_starts_with($file->getMimeType(), 'audio')) {
            $type = 'audio';
        }

        return LessonMaterial::create([
            'lesson_id' => $lessonId,
            'name'      => $name,
            'url'       => $path,
            'type'      => $type,
        ]);
    }

    public function delete(LessonMaterial $material): void
    {
        $this->fileUploadService->delete($material->url);
        $material->delete();
    }
}
