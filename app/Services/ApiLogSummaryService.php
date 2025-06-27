<?php

namespace App\Services;

use App\Models\ApiLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

readonly class ApiLogSummaryService
{
    public function __construct(
        private IpGeolocationService $geoService
    )
    {
    }

    public function getSummary(int $days): array
    {
        $startDate = Carbon::now()->subDays($days);
        $baseQuery = ApiLog::where('created_at', '>=', $startDate);

        return [
            'period' => [
                'days' => $days,
                'start_date' => $startDate->toISOString(),
                'end_date' => Carbon::now()->toISOString(),
            ],
            'overview' => $this->getOverview($baseQuery),
            'endpoints' => $this->getEndpointStats($baseQuery),
            'performance' => $this->getPerformanceStats($baseQuery),
            'security' => $this->getSecurityStats($baseQuery),
            'errors' => $this->getErrorStats($baseQuery),
            'traffic_patterns' => $this->getTrafficPatterns($baseQuery),
            'geographic' => $this->getGeographicStats($baseQuery), // NEW!
        ];
    }

    private function getOverview($query): array
    {
        $baseQuery = clone $query;

        return [
            'total_requests' => $baseQuery->count(),
            'unique_ips' => $baseQuery->distinct('ip_address')->count(),
            'unique_users' => $baseQuery->whereNotNull('user_id')->distinct('user_id')->count(),
            'average_response_time_ms' => round($baseQuery->avg('response_time_ms'), 2),
            'total_data_transferred_mb' => round($baseQuery->sum('response_size') / (1024 * 1024), 2),
            'cache_hit_rate' => $this->calculateCacheHitRate($baseQuery),
        ];
    }

    private function getEndpointStats($query): Collection
    {
        $baseQuery = clone $query;

        return $baseQuery
            ->select('endpoint', 'method')
            ->selectRaw('COUNT(*) as request_count')
            ->selectRaw('AVG(response_time_ms) as avg_response_time')
            ->selectRaw('MAX(response_time_ms) as max_response_time')
            ->selectRaw('SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as error_count')
            ->groupBy('endpoint', 'method')
            ->orderBy('request_count', 'desc')
            ->limit(20)
            ->get()
            ->map(static function ($item) {
                return [
                    'endpoint' => $item->method . ' ' . $item->endpoint,
                    'requests' => $item->request_count,
                    'avg_response_time_ms' => round($item->avg_response_time, 2),
                    'max_response_time_ms' => $item->max_response_time,
                    'error_rate' => round(($item->error_count / $item->request_count) * 100, 2) . '%',
                ];
            });
    }

    private function getPerformanceStats($query): array
    {
        $baseQuery = clone $query;

        $slowRequests = $baseQuery->where('response_time_ms', '>', 1000)->count();
        $verySlowRequests = $baseQuery->where('response_time_ms', '>', 5000)->count();
        $totalRequests = $baseQuery->count();

        return [
            'slow_requests' => [
                'count' => $slowRequests,
                'percentage' => $totalRequests > 0 ? round(($slowRequests / $totalRequests) * 100, 2) : 0,
            ],
            'very_slow_requests' => [
                'count' => $verySlowRequests,
                'percentage' => $totalRequests > 0 ? round(($verySlowRequests / $totalRequests) * 100, 2) : 0,
            ],
            'response_time_percentiles' => $this->getResponseTimePercentiles($baseQuery),
            'slowest_endpoints' => $this->getSlowestEndpoints($baseQuery),
        ];
    }

    private function getSecurityStats($query): array
    {
        $baseQuery = clone $query;

        return [
            'failed_auth_attempts' => $baseQuery->where('status_code', 401)->count(),
            'forbidden_attempts' => $baseQuery->where('status_code', 403)->count(),
            'suspicious_ips' => $this->geoService->enrichSuspiciousIps($this->getSuspiciousIPs($baseQuery)->toArray()),
            'bot_requests' => $this->getBotRequests($baseQuery),
            'admin_access_attempts' => $baseQuery->where('endpoint', 'like', '%admin%')->count(),
        ];
    }

    private function getErrorStats($query): array
    {
        $baseQuery = clone $query;

        return [
            'status_code_breakdown' => $baseQuery
                ->select('status_code')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('status_code')
                ->orderBy('count', 'desc')
                ->get()
                ->mapWithKeys(static function ($item) {
                    return [$item->status_code => $item->count];
                }),
            'error_rate_by_endpoint' => $baseQuery
                ->where('status_code', '>=', 400)
                ->select('endpoint')
                ->selectRaw('COUNT(*) as error_count')
                ->groupBy('endpoint')
                ->orderBy('error_count', 'desc')
                ->limit(10)
                ->get(),
            'most_common_errors' => $baseQuery
                ->whereNotNull('error_message')
                ->select('error_message')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('error_message')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
        ];
    }

    private function getTrafficPatterns($query): array
    {
        $baseQuery = clone $query;
        $driver = config('database.default');

        // Database-specific SQL
        if ($driver === 'mysql') {
            $hourSql = 'HOUR(created_at) as hour';
            $dateSql = 'DATE(created_at) as date';
        } else { // SQLite and others
            $hourSql = "strftime('%H', created_at) as hour";
            $dateSql = "strftime('%Y-%m-%d', created_at) as date";
        }

        return [
            'requests_by_hour' => $baseQuery
                ->selectRaw($hourSql)
                ->selectRaw('COUNT(*) as count')
                ->groupBy('hour')
                ->orderBy('hour')
                ->get()
                ->mapWithKeys(static function ($item) {
                    return [$item->hour . ':00' => $item->count];
                }),
            'requests_by_day' => $baseQuery
                ->selectRaw($dateSql)
                ->selectRaw('COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->mapWithKeys(static function ($item) {
                    return [$item->date => $item->count];
                }),
            'top_user_agents' => $baseQuery
                ->select('user_agent')
                ->selectRaw('COUNT(*) as count')
                ->whereNotNull('user_agent')
                ->groupBy('user_agent')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
        ];
    }

    private function calculateCacheHitRate($query): float
    {
        $baseQuery = clone $query;
        $totalRequests = $baseQuery->count();

        if ($totalRequests === 0) {
            return 0;
        }

        $cacheHits = $baseQuery->where('cache_hit', true)->count();
        return round(($cacheHits / $totalRequests) * 100, 2);
    }

    private function getResponseTimePercentiles($query): array
    {
        $baseQuery = clone $query;
        $responseTimes = $baseQuery->pluck('response_time_ms')->sort()->values();

        if ($responseTimes->isEmpty()) {
            return ['p50' => 0, 'p90' => 0, 'p95' => 0, 'p99' => 0];
        }

        $count = $responseTimes->count();

        return [
            'p50' => $responseTimes[(int)($count * 0.5)],
            'p90' => $responseTimes[(int)($count * 0.9)],
            'p95' => $responseTimes[(int)($count * 0.95)],
            'p99' => $responseTimes[(int)($count * 0.99)],
        ];
    }

    private function getSlowestEndpoints($query): Collection
    {
        $baseQuery = clone $query;

        return $baseQuery
            ->select('endpoint', 'method')
            ->selectRaw('AVG(response_time_ms) as avg_response_time')
            ->selectRaw('MAX(response_time_ms) as max_response_time')
            ->selectRaw('COUNT(*) as request_count')
            ->groupBy('endpoint', 'method')
            ->orderBy('avg_response_time', 'desc')
            ->limit(10)
            ->get()
            ->map(static function ($item) {
                return [
                    'endpoint' => $item->method . ' ' . $item->endpoint,
                    'avg_response_time_ms' => round($item->avg_response_time, 2),
                    'max_response_time_ms' => $item->max_response_time,
                    'request_count' => $item->request_count,
                ];
            });
    }

    private function getSuspiciousIPs($query): Collection
    {
        $baseQuery = clone $query;

        return $baseQuery
            ->select('ip_address')
            ->selectRaw('COUNT(*) as request_count')
            ->selectRaw('SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as error_count')
            ->selectRaw('COUNT(DISTINCT endpoint) as unique_endpoints')
            ->groupBy('ip_address')
            ->havingRaw('request_count > 100 OR error_count > 20')
            ->orderBy('request_count', 'desc')
            ->limit(10)
            ->get()
            ->map(static function ($item) {
                return [
                    'ip' => $item->ip_address,
                    'requests' => $item->request_count,
                    'errors' => $item->error_count,
                    'error_rate' => round(($item->error_count / $item->request_count) * 100, 2) . '%',
                    'unique_endpoints' => $item->unique_endpoints,
                ];
            });
    }

    private function getBotRequests($query): array
    {
        $baseQuery = clone $query;

        $botPatterns = ['bot', 'crawler', 'spider', 'curl', 'wget'];

        $botStats = [];
        foreach ($botPatterns as $pattern) {
            $count = $baseQuery->where('user_agent', 'like', "%{$pattern}%")->count();
            if ($count > 0) {
                $botStats[$pattern] = $count;
            }
        }

        return $botStats;
    }

    private function getGeographicStats($query): array
    {
        $baseQuery = clone $query;

        // Get IP counts
        $ipCounts = $baseQuery
            ->select('ip_address')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('ip_address')
            ->get()
            ->mapWithKeys(static function ($item) {
                return [$item->ip_address => $item->count];
            })
            ->toArray();

        // Get country stats
        $countryStats = $this->geoService->getCountryStats($ipCounts);

        // Get top cities
        $topCities = $this->getTopCities($baseQuery);

        return [
            'countries' => array_slice($countryStats, 0, 10), // Top 10 countries
            'cities' => $topCities,
            'total_countries' => count($countryStats),
            'most_active_country' => $countryStats[0] ?? null,
        ];
    }

    private function getTopCities($query): array
    {
        $baseQuery = clone $query;

        // Get unique IPs
        $uniqueIps = $baseQuery->distinct('ip_address')->pluck('ip_address')->toArray();

        // Look up locations for unique IPs
        $cities = [];
        foreach ($uniqueIps as $ip) {
            $location = $this->geoService->getLocationData($ip);
            if ($location && $location['city'] !== 'Unknown') {
                $cityKey = $location['city'] . ', ' . $location['country'];

                if (!isset($cities[$cityKey])) {
                    $cities[$cityKey] = [
                        'city' => $location['city'],
                        'country' => $location['country'],
                        'country_code' => $location['country_code'],
                        'request_count' => 0,
                        'unique_ips' => 0
                    ];
                }

                // Count requests from this IP
                $ipRequests = $baseQuery->where('ip_address', $ip)->count();
                $cities[$cityKey]['request_count'] += $ipRequests;
                $cities[$cityKey]['unique_ips']++;
            }
        }

        // Sort by request count and return top 10
        uasort($cities, static fn($a, $b) => $b['request_count'] <=> $a['request_count']);

        return array_slice(array_values($cities), 0, 10);
    }
}
