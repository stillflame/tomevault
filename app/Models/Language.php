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
class Language extends Model
{
    use HasFactory, UsesUuid;

    protected $fillable = ['name', 'notes'];

    public function tomes(): HasMany
    {
        return $this->hasMany(Tome::class);
    }
}
