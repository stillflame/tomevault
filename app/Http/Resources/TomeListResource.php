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

            // ✅ Fixed: Only include author if relationship is loaded and exists
            'author' => $this->when($this->relationLoaded('author') && $this->author, [
                'name' => $this->author->name,
            ]),

            // ✅ Fixed: Safe null checking for language
            'language_name' => $this->when($this->relationLoaded('language') && $this->language, $this->language->name),

            'danger_level' => $this->danger_level->value,
            'cursed' => $this->cursed,
            'sentient' => $this->sentient,
            'pages' => $this->pages,
            'illustrated' => $this->illustrated,

            // ✅ Fixed: Safe checking for current owner
            'current_owner' => $this->when(
                $this->relationLoaded('currentOwner') && $this->currentOwner,
                fn() => [
                    'name' => $this->currentOwner->name,
                ]
            ),

            // ✅ Fixed: Use proper count attribute name
            'spell_count' => $this->when($this->spells_count > 0, $this->spells_count),

            'tome_detail_url' => url('/') . config('api.api_prefix') . '/tomes/' . $this->id,
        ];
    }
}
