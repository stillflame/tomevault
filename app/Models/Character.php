<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 * @mixin Builder
 */
class Character extends Model
{
    use HasFactory, UsesUuid;

    protected $fillable = ['name', 'bio'];

    public function authoredTomes(): HasMany
    {
        return $this->hasMany(Tome::class, 'author_id');
    }

    public function ownedTomes(): HasMany
    {
        return $this->hasMany(Tome::class, 'current_owner_id');
    }
}
