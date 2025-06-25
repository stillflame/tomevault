<?php

namespace App\Models;

use App\Enums\DangerLevel;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 * @mixin Builder
 */
class Spell extends Model
{
    use HasFactory, UsesUuid;

    protected $fillable = [
        'name',
        'effect',
        'danger_level',
        'tome_id',
    ];

    protected $casts = [
        'danger_level' => DangerLevel::class,
    ];

    public function tome(): BelongsTo
    {
        return $this->belongsTo(Tome::class);
    }
}
