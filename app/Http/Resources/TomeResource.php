<?php

namespace App\Http\Resources;

use App\Helpers\TomeHelper;
use App\Models\Tome;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Tome $resource
 */
class TomeResource extends JsonResource
{

    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'alternate_titles' => $this->alternate_titles,
            'origin' => $this->origin,

            // Nested relationships as full objects
            'author' => $this->whenLoaded('author', fn() => new CharacterResource($this->author)),
            'language' => $this->whenLoaded('language', fn() => new LanguageResource($this->language)),
            'current_owner' => $this->whenLoaded('currentOwner', fn() => new CharacterResource($this->currentOwner)),
            'last_known_location' => $this->whenLoaded('lastKnownLocation', fn() => new LocationResource($this->lastKnownLocation)),

            'contents_summary' => $this->contents_summary,
            'cursed' => $this->cursed,
            'sentient' => $this->sentient,
            'danger_level' => $this->danger_level->value,
            'artifact_type' => $this->artifact_type,
            'cover_material' => $this->cover_material,
            'pages' => $this->pages,
            'illustrated' => $this->illustrated,
            'notable_quotes' => collect($this->notable_quotes)->map(fn($quote) => trim($quote, '"'))->all(),

            // Collection of nested spells as full objects - fixed to handle null
            'spells' => $this->whenLoaded('spells', fn() => SpellResource::collection($this->spells)),
            'tome_detail_url' => TomeHelper::resourceUrl($this->id),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
