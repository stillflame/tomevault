<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 * @mixin Builder
 */
class ApiLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'request_id',
        'method',
        'url',
        'endpoint',
        'ip_address',
        'user_agent',
        'user_id',
        'user_type',
        'status_code',
        'response_time_ms',
        'response_size',
        'request_headers',
        'request_data',
        'response_data',
        'cache_hit',
        'log_level',
        'error_message',
        'error_context',
        'metadata',
    ];

    protected $casts = [
        'request_headers' => 'array',
        'request_data' => 'array',
        'response_data' => 'array',
        'error_context' => 'array',
        'metadata' => 'array',
        'cache_hit' => 'boolean',
        'response_time_ms' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes for common queries
    public function scopeErrors($query)
    {
        return $query->where('status_code', '>=', 400);
    }

    public function scopeSlowRequests($query, $threshold = 1000)
    {
        return $query->where('response_time_ms', '>', $threshold);
    }

    public function scopeByEndpoint($query, $endpoint)
    {
        return $query->where('endpoint', $endpoint);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByIp($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
}
