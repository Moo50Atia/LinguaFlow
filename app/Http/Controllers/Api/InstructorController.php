<?php

namespace App\Http\Controllers\Api;

use App\Models\Instructor;
use App\Queries\Instructor\InstructorCatalogQuery;
use App\Queries\Instructor\InstructorSlotsQuery;
use App\Http\Resources\InstructorResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstructorController extends BaseController
{
    /**
     * Get a catalog of instructors.
     */
    public function index(Request $request, InstructorCatalogQuery $query): JsonResponse
    {
        $instructors = $query->execute($request->all());

        return $this->sendSuccess([
            'instructors' => InstructorResource::collection($instructors)->response()->getData(true)
        ], 'Instructors retrieved.');
    }

    /**
     * Show instructor profile.
     */
    public function show(Instructor $instructor): JsonResponse
    {
        $instructor->load(['user', 'courses' => function($q) {
            $q->where('is_published', true);
        }]);
        $instructor->loadCount('reviews');

        return $this->sendSuccess([
            'instructor' => new InstructorResource($instructor)
        ]);
    }

    /**
     * Get an instructor's available slots.
     */
    public function slots(Request $request, Instructor $instructor, InstructorSlotsQuery $query): JsonResponse
    {
        $slots = $query->execute($instructor->id, $request->get('month'), $request->get('year'));

        return $this->sendSuccess([
            'slots' => $slots
        ]);
    }
}
