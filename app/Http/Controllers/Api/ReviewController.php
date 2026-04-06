<?php

namespace App\Http\Controllers\Api;

use App\Models\Course;
use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReviewController extends BaseController
{
    /**
     * Store a new review for a course or instructor.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reviewable_id'   => 'required|integer',
            'reviewable_type' => 'required|in:course,instructor',
            'rating'          => 'required|integer|min:1|max:5',
            'comment'         => 'nullable|string|max:1000',
        ]);

        // Simple polymorphic attachment logic
        $typeMap = [
            'course'     => Course::class,
            'instructor' => Instructor::class,
        ];
        
        $modelClass = $typeMap[$validated['reviewable_type']];
        $model = $modelClass::findOrFail($validated['reviewable_id']);

        $review = $model->reviews()->create([
            'user_id' => $request->user()->id,
            'rating'  => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return $this->sendCreated(['review' => $review], 'Review submitted successfully.');
    }
}
