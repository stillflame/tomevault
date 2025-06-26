<?php

namespace App\Http\Requests;

use App\Enums\ArtifactType;
use App\Enums\CoverMaterial;
use App\Enums\DangerLevel;
use App\Models\Character;
use App\Models\Language;
use App\Models\Location;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreTomeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'alternate_titles' => 'array',
            'alternate_titles.*' => 'string|max:255',
            'origin' => 'nullable|string|max:255',

            // Smart ID input fields
            'author' => 'nullable|string|max:255',
            'current_owner' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:255',
            'last_known_location' => 'nullable|string|max:255',

            // Resolved UUID fields
            'author_id' => 'nullable|uuid|exists:characters,id',
            'language_id' => 'nullable|uuid|exists:languages,id',
            'current_owner_id' => 'nullable|uuid|exists:characters,id',
            'last_known_location_id' => 'nullable|uuid|exists:locations,id',

            'contents_summary' => 'nullable|string',
            'cursed' => 'boolean',
            'sentient' => 'boolean',
            'danger_level' => ['nullable', Rule::enum(DangerLevel::class)],
            'artifact_type' => ['nullable', Rule::enum(ArtifactType::class)],
            'cover_material' => ['nullable', Rule::enum(CoverMaterial::class)],
            'pages' => 'nullable|integer|min:1',
            'illustrated' => 'boolean',
            'notable_quotes' => 'array',
            'notable_quotes.*' => 'string|max:1000', // Reasonable limit for quotes
        ];
    }

    public function prepareForValidation(): void
    {
        $this->resolveSmartId('author', Character::class, 'author_id');
        $this->resolveSmartId('current_owner', Character::class, 'current_owner_id');
        $this->resolveSmartId('language', Language::class, 'language_id');
        $this->resolveSmartId('last_known_location', Location::class, 'last_known_location_id');
    }

    /**
     * @param string $inputKey
     * @param class-string<Model> $modelClass
     * @param string $targetKey
     * @throws ValidationException
     */
    protected function resolveSmartId(string $inputKey, string $modelClass, string $targetKey): void
    {
        if (!$this->has($inputKey)) {
            return;
        }

        $value = $this->input($inputKey);

        if (!is_string($value) || trim($value) === '') {
            return;
        }

        // Sanitize input - remove potentially dangerous characters
        $sanitizedValue = trim($value);

        // First try to find by UUID (exact match)
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $sanitizedValue)) {
            $record = $modelClass::find($sanitizedValue);
        } else {
            // Then try to find by name (case-insensitive, exact match)
            $record = $modelClass::whereRaw('LOWER(name) = ?', [strtolower($sanitizedValue)])->first();
        }

        if ($record) {
            $this->merge([$targetKey => $record->id]);
        } else {
            // Optionally throw validation error for invalid references
            // You can uncomment this if you want strict validation
            /*
            throw ValidationException::withMessages([
                $inputKey => "The selected {$inputKey} is invalid."
            ]);
            */
        }
    }
}
