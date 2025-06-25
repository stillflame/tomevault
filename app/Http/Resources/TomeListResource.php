<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TomeListResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => [
                'id' => $this->author->id,
                'name' => $this->author->name,
            ],
            'language_name' => $this->language ? $this->language->name : null,
            'danger_level' => $this->danger_level->value,

            // ONLY include spell_count key IF spells_count > 0
            'spell_count' => $this->when($this->spells_count > 0, $this->spells_count),
            'tome_detail_url' => url('/') . config('api.api_prefix').'/tomes/' . $this->id
        ];
    }

}
