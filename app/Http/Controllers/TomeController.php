<?php

namespace App\Http\Controllers;

use App\Models\Tome;
use App\Services\TomeService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
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

        return $this->success($result);
    }

    /**
     * @throws JsonException
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'alternate_titles' => 'array',
            'alternate_titles.*' => 'string',
            'origin' => 'nullable|string|max:255',
            'author_id' => 'nullable|uuid|exists:characters,id',
            'language_id' => 'nullable|uuid|exists:languages,id',
            'current_owner_id' => 'nullable|uuid|exists:characters,id',
            'last_known_location_id' => 'nullable|uuid|exists:locations,id',
            'contents_summary' => 'nullable|string',
            'cursed' => 'boolean',
            'sentient' => 'boolean',
            'danger_level' => 'in:Low,Medium,High,Severe,Unknown',
            'artifact_type' => 'nullable|string|max:255',
            'cover_material' => 'nullable|string|max:255',
            'pages' => 'nullable|integer',
            'illustrated' => 'boolean',
            'notable_quotes' => 'array',
            'notable_quotes.*' => 'string',
        ]);

        // Save JSON fields properly (convert arrays to JSON)
        if (isset($validated['alternate_titles'])) {
            $validated['alternate_titles'] = json_encode($validated['alternate_titles'], JSON_THROW_ON_ERROR);
        }

        if (isset($validated['notable_quotes'])) {
            $validated['notable_quotes'] = json_encode($validated['notable_quotes'], JSON_THROW_ON_ERROR);
        }

        $tome = Tome::create($validated);

        return response()->json($tome, 201);
    }


}
