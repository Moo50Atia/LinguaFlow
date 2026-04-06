<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    /**
     * Store an uploaded file.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @return string
     */
    public function store(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, 'public');
        return Storage::url($path);
    }

    /**
     * Delete a file from storage.
     *
     * @param string|null $url
     * @return void
     */
    public function delete(?string $url): void
    {
        if (!$url) {
            return;
        }

        // Convert storage URL back to path relative to public disk
        $path = str_replace(url('/storage') . '/', '', $url);
        
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
