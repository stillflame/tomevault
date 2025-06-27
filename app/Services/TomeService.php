<?php

namespace App\Services;

use App\Http\Resources\TomeListResource;
use App\Models\Tome;
use App\Http\Resources\TomeResource;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use JsonException;

class TomeService
{
    private const DEFAULT_PER_PAGE = 10;
    private const DEFAULT_PAGINATE_THRESHOLD = 10;

    /**
     * Get paginated lightweight tomes with metadata for index.
     */
    public function getTomesForIndex(int|null $perPage = null, int $paginateThreshold = self::DEFAULT_PAGINATE_THRESHOLD): array
    {
        $perPage ??= self::DEFAULT_PER_PAGE;

        // Cache the count for 5 minutes if data is relatively static
//        $totalTomes = Cache::remember('tomes_total_count', 300, function () {
//        return Tome::count();
//        });

        $totalTomes = Tome::count();

        // Early return for empty data - avoid expensive query building
        if ($totalTomes === 0) {
            return [
                'data' => [],
                'meta' => [
                    'total' => 0,
                ],
            ];
        }

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
    public function createTome(array $data, Authenticatable|null $user = null): Tome
    {
        $processedData = $this->processJsonFields($data);

        if ($user) {
            $processedData['created_by'] = $user->getAuthIdentifier();
        }

        return Tome::create($processedData);
    }

    private function buildTomeQuery(): Builder
    {
        return Tome::select([
            'id', 'slug', 'title', 'origin', 'artifact_type',
            'author_id', 'language_id', 'current_owner_id',
            'danger_level', 'cursed', 'sentient', 'pages',
            'illustrated', 'created_at', 'updated_at'
        ])
            ->with([
                'author:id,name',
                'language:id,name',
                'currentOwner:id,name'
            ])
            ->withCount('spells');
    }

    private function getPaginatedTomes($query, int $perPage): array
    {
        $paginator = $query->paginate($perPage);

        // Skip resource transformation if empty
        if ($paginator->count() === 0) {
            return [
                'data' => [],
                'meta' => [
                    'total' => $paginator->total(),
                    'count' => 0,
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'next_page_url' => null,
                    'prev_page_url' => null,
                ],
            ];
        }

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
        $queryStart = microtime(true);
        $tomes = $query->get();
        $queryTime = (microtime(true) - $queryStart) * 1000;

        if ($tomes->isEmpty()) {
            return ['data' => [], 'meta' => ['total' => 0]];
        }

        $resourceStart = microtime(true);
        $data = TomeListResource::collection($tomes)->resolve();
        $resourceTime = (microtime(true) - $resourceStart) * 1000;

        Log::info('TomeService Timing', [
            'query_time_ms' => round($queryTime, 2),
            'resource_time_ms' => round($resourceTime, 2),
            'record_count' => $tomes->count()
        ]);

        return [
            'data' => $data,
            'meta' => ['total' => $tomes->count()],
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
