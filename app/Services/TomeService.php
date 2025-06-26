<?php

namespace App\Services;

use App\Http\Resources\TomeListResource;
use App\Models\Tome;
use App\Http\Resources\TomeResource;
use JsonException;

class TomeService
{
    /**
     * Get paginated lightweight tomes with metadata for index.
     *
     * @param int|null $perPage
     * @param int $paginateThreshold
     * @return array
     */
    public function getTomesForIndex(int|null $perPage = null, int $paginateThreshold = 10): array
    {
        $perPage ??= 10;
        $totalTomes = Tome::count();

        $query = Tome::with(['author', 'language'])->withCount('spells');

        if ($totalTomes > $paginateThreshold) {
            $paginator = $query->paginate($perPage);
            $data = TomeListResource::collection($paginator->getCollection());
            $meta = [
                'total' => $paginator->total(),
                'count' => $paginator->count(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'next_page_url' => $paginator->nextPageUrl(),
                'prev_page_url' => $paginator->previousPageUrl(),
                'timestamp' => now()->toIso8601String(),
            ];

            return [
                'data' => $data->resolve(),
                'meta' => $meta,
            ];
        }

        $tomes = $query->get();
        $data = TomeListResource::collection($tomes);
        $meta = [
            'total' => $totalTomes,
            'timestamp' => now()->toIso8601String(),
        ];

        return [
            'data' => $data->resolve(),
            'meta' => $meta,
        ];
    }


    /**
     * Get detailed tome by ID with nested relationships.
     *
     * @param Tome $tome
     * @return TomeResource|null
     */
    public function getTomeDetail(Tome $tome): TomeResource|null
    {
        $tome->load([
            'author',
            'language',
            'currentOwner',
            'lastKnownLocation',
            'spells',
        ]);


        return new TomeResource($tome);
    }

    /**
     * @throws JsonException
     */
    public function createTome(array $data): Tome
    {
        // Ensure JSON fields are encoded
        if (isset($data['alternate_titles'])) {
            $data['alternate_titles'] = json_encode($data['alternate_titles'], JSON_THROW_ON_ERROR);
        }

        if (isset($data['notable_quotes'])) {
            $data['notable_quotes'] = json_encode($data['notable_quotes'], JSON_THROW_ON_ERROR);
        }

        return Tome::create($data);
    }

}
