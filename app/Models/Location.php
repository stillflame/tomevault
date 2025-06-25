<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 * @mixin Builder
 */
class Location extends Model
{
    use HasFactory, UsesUuid;

    protected $fillable = ['name', 'realm', 'description'];

    public function tomes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Tome::class, 'last_known_location_id');
    }
}
