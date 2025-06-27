<?php

namespace App\Http\Controllers;

use App\Services\ApiLogSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiLogSummaryController extends Controller
{
    public function __construct(
        private readonly ApiLogSummaryService $summaryService
    ) {}

    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'integer|min:1|max:365'
        ]);

        $days = $request->get('days', 7);
        $summary = $this->summaryService->getSummary($days);

        return response()->json($summary);
    }
}
