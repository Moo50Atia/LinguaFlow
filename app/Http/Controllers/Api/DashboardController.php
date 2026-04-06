<?php

namespace App\Http\Controllers\Api;

use App\Queries\Analytics\StudentDashboardQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends BaseController
{
    /**
     * Get the student dashboard telemetry.
     */
    public function index(Request $request, StudentDashboardQuery $query): JsonResponse
    {
        $dashboardData = $query->execute($request->user()->id);

        return $this->sendSuccess([
            'dashboard' => $dashboardData
        ]);
    }
}
