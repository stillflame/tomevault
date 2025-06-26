<?php

namespace App\Models;

use App\Enums\DangerLevel;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 * @mixin Builder
 */
class Tome extends Model
{
    use HasFactory, UsesUuid;


    protected $fillable = [
        'title',
        'alternate_titles',
        'origin',
        'author_id',
        'language_id',
        'contents_summary',
        'cursed',
        'sentient',
        'current_owner_id',
        'last_known_location_id',
        'danger_level',
        'artifact_type',
        'cover_material',
        'pages',
        'illustrated',
        'notable_quotes',
    ];

    protected $casts = [
        'alternate_titles' => 'array',
        'notable_quotes' => 'array',
        'cursed' => 'boolean',
        'sentient' => 'boolean',
        'illustrated' => 'boolean',
        'danger_level' => DangerLevel::class,
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'author_id');
    }

    public function currentOwner(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'current_owner_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function lastKnownLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'last_known_location_id');
    }

    public function spells(): HasMany
    {
        return $this->hasMany(Spell::class);
    }
}
