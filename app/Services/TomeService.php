<?php

namespace App\Services;

use App\Http\Resources\TomeListResource;
use App\Models\Tome;
use App\Http\Resources\TomeResource;
use JsonException;

class TomeService
{
    private const DEFAULT_PER_PAGE = 10;
    private const DEFAULT_PAGINATE_THRESHOLD = 10;

    /**
     * Get paginated lightweight tomes with metadata for index.
     */
    public function getTomesForIndex(?int $perPage = null, int $paginateThreshold = self::DEFAULT_PAGINATE_THRESHOLD): array
    {
        $perPage ??= self::DEFAULT_PER_PAGE;
        $totalTomes = Tome::count();
        $query = $this->buildTomeQuery();

        if ($totalTomes > $paginateThreshold) {
            return $this->getPaginatedTomes($query, $perPage);
        }

        return $this->getAllTomes($query);
    }

    /**
     * Get detailed tome by ID with nested relationships.
     */
    public function getTomeDetail(Tome $tome): array
    {
        $tome->load($this->getDetailRelationships());

        return [
            'data' => new TomeResource($tome),
            'meta' => [], // Let ApiResponses trait handle timestamps
        ];
    }

    /**
     * Create a new tome with JSON field encoding.
     *
     * @throws JsonException
     */
    public function createTome(array $data): Tome
    {
        $processedData = $this->processJsonFields($data);
        return Tome::create($processedData);
    }

    private function buildTomeQuery()
    {
        return Tome::with(['author', 'language', 'currentOwner'])->withCount('spells');
    }

    private function getPaginatedTomes($query, int $perPage): array
    {
        $paginator = $query->paginate($perPage);
        $data = TomeListResource::collection($paginator->getCollection())->resolve();

        return [
            'data' => $data,
            'meta' => [
                'total' => $paginator->total(),
                'count' => $paginator->count(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'next_page_url' => $paginator->nextPageUrl(),
                'prev_page_url' => $paginator->previousPageUrl(),
            ],
        ];
    }

    private function getAllTomes($query): array
    {
        $tomes = $query->get();
        $data = TomeListResource::collection($tomes)->resolve();

        return [
            'data' => $data,
            'meta' => [
                'total' => $tomes->count(),
            ],
        ];
    }

    private function getDetailRelationships(): array
    {
        return [
            'author',
            'language',
            'currentOwner',
            'lastKnownLocation',
            'spells',
        ];
    }

    /**
     * @throws JsonException
     */
    private function processJsonFields(array $data): array
    {
        $jsonFields = ['alternate_titles', 'notable_quotes'];

        foreach ($jsonFields as $field) {
            $value = data_get($data, $field);
            if ($value !== null) {
                data_set($data, $field, json_encode($value, JSON_THROW_ON_ERROR));
            }
        }

        return $data;
    }
}
