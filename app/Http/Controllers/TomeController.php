<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTomeRequest;
use App\Http\Resources\TomeResource;
use App\Models\Tome;
use App\Services\TomeService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use JsonException;

class TomeController
{

    use ApiResponses;

    protected TomeService $tomeService;

    public function __construct(TomeService $tomeService)
    {
        $this->tomeService = $tomeService;
    }

    public function index(): JsonResponse
    {
        $result = $this->tomeService->getTomesForIndex();

        return $this->success($result['data'], null, $result['meta']);
    }
//    public function index()
//    {
//        $start = microtime(true);
//
//        // Time just the service call
//        $serviceStart = microtime(true);
//        $result = $this->tomeService->getTomesForIndex();
//        $serviceTime = (microtime(true) - $serviceStart) * 1000;
//
//        // Time the JSON response creation
//        $responseStart = microtime(true);
//        $response = response()->json($result);
//        $responseTime = (microtime(true) - $responseStart) * 1000;
//
//        $totalTime = (microtime(true) - $start) * 1000;
//
//        Log::info('Detailed Controller Timing', [
//            'service_time_ms' => round($serviceTime, 2),
//            'response_creation_ms' => round($responseTime, 2),
//            'total_controller_ms' => round($totalTime, 2),
//            'database_time_ms' => '~3ms', // We know this from logs
//            'missing_time_ms' => round($serviceTime - 3, 2) // What's unaccounted for
//        ]);
//
//        return $this->success($result['data'], null, $result['meta']);
//    }

    public function show(Tome $tome): JsonResponse
    {

        $result = $this->tomeService->getTomeDetail($tome);
        return $this->success($result['data'], null, $result['meta']);
    }


    /**
     * @throws JsonException
     */
    public function store(StoreTomeRequest $request): JsonResponse
    {
        $user = $request->user(); // get authenticated user via Sanctum

        $tome = $this->tomeService->createTome($request->validated(), $user);

        return $this->success(new TomeResource($tome), 'Tome created', null, 201);
    }


}
