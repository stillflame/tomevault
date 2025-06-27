<?php

namespace App\Services;

use App\Models\ApiLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
            'geographic' => $this->getGeographicStats($baseQuery),
        ];
    }

    private function getOverview($query): array
    {
        $baseQuery = clone $query;

        return [
            'total_requests' => $baseQuery->count(),
            'unique_ips' => $baseQuery->distinct('ip_address')->count('ip_address'),
            'unique_users' => $baseQuery->whereNotNull('user_id')->distinct('user_id')->count('user_id'),
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
            ->orderByDesc(DB::raw('COUNT(*)'))
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
                ->orderByDesc(DB::raw('COUNT(*)'))
                ->get()
                ->mapWithKeys(static function ($item) {
                    return [$item->status_code => $item->count];
                }),

            'error_rate_by_endpoint' => $baseQuery
                ->where('status_code', '>=', 400)
                ->select('endpoint')
                ->selectRaw('COUNT(*) as error_count')
                ->groupBy('endpoint')
                ->orderByDesc(DB::raw('COUNT(*)'))
                ->limit(10)
                ->get(),

            'most_common_errors' => $baseQuery
                ->whereNotNull('error_message')
                ->select('error_message')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('error_message')
                ->orderByDesc(DB::raw('COUNT(*)'))
                ->limit(10)
                ->get(),
        ];
    }

    private function getTrafficPatterns($query): array
    {
        $baseQuery = clone $query;
        $driver = config('database.default');

        $hourSql = $driver === 'mysql' ? 'HOUR(created_at)' : "CAST(strftime('%H', created_at) AS INTEGER)";
        $dateSql = $driver === 'mysql' ? 'DATE(created_at)' : "strftime('%Y-%m-%d', created_at)";

        return [
            'requests_by_hour' => $baseQuery
                ->selectRaw("{$hourSql} as hour")
                ->selectRaw('COUNT(*) as count')
                ->groupBy('hour')
                ->orderBy('hour')
                ->get()
                ->mapWithKeys(static function ($item) {
                    return [sprintf('%02d:00', $item->hour) => $item->count];
                }),

            'requests_by_day' => $baseQuery
                ->selectRaw("{$dateSql} as date")
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
                ->orderByDesc(DB::raw('COUNT(*)'))
                ->limit(10)
                ->get(),
        ];
    }

    private function getResponseTimePercentiles($query): array
    {
        $times = $query->pluck('response_time_ms')->sort()->values();
        if ($times->isEmpty()) {
            return ['p50' => 0, 'p90' => 0, 'p95' => 0, 'p99' => 0];
        }

        $count = $times->count();
        return [
            'p50' => $times[(int)($count * 0.5)],
            'p90' => $times[(int)($count * 0.9)],
            'p95' => $times[(int)($count * 0.95)],
            'p99' => $times[(int)($count * 0.99)],
        ];
    }

    private function getSlowestEndpoints($query): Collection
    {
        return $query
            ->select('endpoint', 'method')
            ->selectRaw('AVG(response_time_ms) as avg_response_time')
            ->selectRaw('MAX(response_time_ms) as max_response_time')
            ->selectRaw('COUNT(*) as request_count')
            ->groupBy('endpoint', 'method')
            ->orderByDesc(DB::raw('AVG(response_time_ms)'))
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

    private function calculateCacheHitRate($query): float
    {
        $total = $query->count();
        if ($total === 0) {
            return 0;
        }
        return round($query->where('cache_hit', true)->count() / $total * 100, 2);
    }

    private function getBotRequests($query): array
    {
        $patterns = ['bot', 'crawler', 'spider', 'curl', 'wget'];
        $stats = [];
        foreach ($patterns as $pattern) {
            $count = (clone $query)->where('user_agent', 'like', "%{$pattern}%")->count();
            if ($count > 0) {
                $stats[$pattern] = $count;
            }
        }
        return $stats;
    }

    private function getSuspiciousIPs($query): Collection
    {
        return $query
            ->select('ip_address')
            ->selectRaw('COUNT(*) as request_count')
            ->selectRaw('SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as error_count')
            ->selectRaw('COUNT(DISTINCT endpoint) as unique_endpoints')
            ->groupBy('ip_address')
            ->havingRaw('request_count > 100 OR error_count > 20')
            ->orderByDesc(DB::raw('request_count'))
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

    private function getGeographicStats($query): array
    {
        $ipCounts = (clone $query)
            ->select('ip_address')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('ip_address')
            ->get()
            ->mapWithKeys(static fn($item) => [$item->ip_address => $item->count])
            ->toArray();

        $countries = $this->geoService->getCountryStats($ipCounts);
        $cities = $this->getTopCities($query);

        return [
            'countries' => array_slice($countries, 0, 10),
            'cities' => $cities,
            'total_countries' => count($countries),
            'most_active_country' => $countries[0] ?? null,
        ];
    }

    private function getTopCities($query): array
    {
        $uniqueIps = (clone $query)->distinct('ip_address')->pluck('ip_address')->toArray();
        $cities = [];

        foreach ($uniqueIps as $ip) {
            $location = $this->geoService->getLocationData($ip);
            if ($location && $location['city'] !== 'Unknown') {
                $key = $location['city'] . ', ' . $location['country'];

                if (!isset($cities[$key])) {
                    $cities[$key] = [
                        'city' => $location['city'],
                        'country' => $location['country'],
                        'country_code' => $location['country_code'],
                        'request_count' => 0,
                        'unique_ips' => 0,
                    ];
                }

                $cities[$key]['request_count'] += (clone $query)->where('ip_address', $ip)->count();
                $cities[$key]['unique_ips']++;
            }
        }

        uasort($cities, static fn($a, $b) => $b['request_count'] <=> $a['request_count']);
        return array_slice(array_values($cities), 0, 10);
    }
}
