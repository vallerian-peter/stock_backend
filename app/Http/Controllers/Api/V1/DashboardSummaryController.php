<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DashboardSummaryService;
use Illuminate\Http\JsonResponse;

class DashboardSummaryController extends Controller
{
    public function __invoke(DashboardSummaryService $service): JsonResponse
    {
        return response()->json(['data' => $service->getSummary()]);
    }
}
