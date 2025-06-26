<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTomeRequest;
use App\Http\Resources\TomeResource;
use App\Models\Tome;
use App\Services\TomeService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
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
        $tome = $this->tomeService->createTome($request->validated());

        return $this->success(new TomeResource($tome), 'Tome created', null, 201);
    }


}
