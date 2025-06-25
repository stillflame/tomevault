<?php

namespace App\Services;

use App\Http\Resources\TomeListResource;
use App\Models\Tome;
use App\Http\Resources\TomeResource;

class TomeService
{
    /**
     * Get paginated lightweight tomes with metadata for index.
     *
     * @param int|null $perPage
     * @param int $paginateThreshold
     * @return array
     */
    public function getTomesForIndex(int $perPage = 10, int $paginateThreshold = 10): array
    {
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
        } else {
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
}
