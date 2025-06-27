<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TomeListResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'origin' => $this->origin,
            'artifact_type' => $this->artifact_type,
            'author' => [

                'name' => $this->author->name,
            ],
            'language_name' => $this->language ? $this->language->name : null,
            'danger_level' => $this->danger_level->value,
            'cursed' => $this->cursed,
            'sentient' => $this->sentient,
            'pages' => $this->pages,
            'illustrated' => $this->illustrated,

            // Current owner info (useful to know availability)
            'current_owner' => $this->when($this->currentOwner, [

                'name' => $this->currentOwner?->name,
            ]),

            // ONLY include spell_count key IF spells_count > 0
            'spell_count' => $this->when($this->spells_count > 0, $this->spells_count),
            'tome_detail_url' => url('/') . config('api.api_prefix') . '/tomes/' . $this->id,

        ];
    }
}
