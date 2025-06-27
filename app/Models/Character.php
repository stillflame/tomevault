<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 * @mixin Builder
 */
class Character extends Model
{
    use HasFactory, UsesUuid, HasSlug;

    protected $fillable = ['name', 'bio', 'slug'];

    public function authoredTomes(): HasMany
    {
        return $this->hasMany(Tome::class, 'author_id');
    }

    public function ownedTomes(): HasMany
    {
        return $this->hasMany(Tome::class, 'current_owner_id');
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');

    }
}
