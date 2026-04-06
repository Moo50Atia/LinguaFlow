<?php

namespace App\Http\Controllers\Api;

use App\Queries\Analytics\InstructorDashboardQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstructorDashboardController extends BaseController
{
    /**
     * Get the instructor dashboard telemetry.
     */
    public function index(Request $request, InstructorDashboardQuery $query): JsonResponse
    {
        $instructorId = $request->user()->instructor->id;
        $dashboardData = $query->execute($instructorId);

        return $this->sendSuccess([
            'dashboard' => $dashboardData
        ]);
    }
}
