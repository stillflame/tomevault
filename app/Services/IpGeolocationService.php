<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IpGeolocationService
{
    private const CACHE_TTL = 86400; // 24 hours
    private const API_URL = 'http://ip-api.com/json/';

    public function getLocationData(string $ip): array|null
    {
        // Skip local/private IPs
        if ($this->isLocalIp($ip)) {
            return [
                'country' => 'Local',
                'country_code' => 'LOCAL',
                'region' => 'Local Network',
                'city' => 'Local',
                'timezone' => config('app.timezone'),
                'isp' => 'Local Network'
            ];
        }

        $cacheKey = "ip_location_{$ip}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($ip) {
            return $this->fetchLocationData($ip);
        });
    }

    public function getCountryStats(array $ips): array
    {
        $countries = [];

        foreach ($ips as $ip => $count) {
            $location = $this->getLocationData($ip);
            $country = $location['country'] ?? 'Unknown';
            $countryCode = $location['country_code'] ?? 'XX';

            if (!isset($countries[$country])) {
                $countries[$country] = [
                    'country' => $country,
                    'country_code' => $countryCode,
                    'request_count' => 0,
                    'unique_ips' => 0
                ];
            }

            $countries[$country]['request_count'] += $count;
            $countries[$country]['unique_ips']++;
        }

        // Sort by request count
        uasort($countries, static fn($a, $b) => $b['request_count'] <=> $a['request_count']);

        return array_values($countries);
    }

    public function enrichSuspiciousIps(array $suspiciousIps): array
    {
        return array_map(function ($ipData) {
            $location = $this->getLocationData($ipData['ip']);

            return array_merge($ipData, [
                'country' => $location['country'] ?? 'Unknown',
                'country_code' => $location['country_code'] ?? 'XX',
                'city' => $location['city'] ?? 'Unknown',
                'isp' => $location['isp'] ?? 'Unknown',
                'is_hosting' => $this->isHostingProvider($location['isp'] ?? ''),
                'risk_score' => $this->calculateRiskScore($ipData, $location)
            ]);
        }, $suspiciousIps);
    }

    private function fetchLocationData(string $ip): array|null
    {
        try {
            $response = Http::timeout(5)
                ->retry(2, 100)
                ->get(self::API_URL . $ip, [
                    'fields' => 'status,message,country,countryCode,region,regionName,city,timezone,isp,org,as,query'
                ]);

            if (!$response->successful()) {
                Log::warning("Failed to fetch IP location for {$ip}", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $data = $response->json();

            if ($data['status'] !== 'success') {
                Log::warning("IP API returned error for {$ip}", ['response' => $data]);
                return null;
            }

            return [
                'country' => $data['country'],
                'country_code' => $data['countryCode'],
                'region' => $data['regionName'],
                'city' => $data['city'],
                'timezone' => $data['timezone'],
                'isp' => $data['isp'],
                'org' => $data['org'] ?? $data['isp'],
                'as' => $data['as'] ?? null,
            ];

        } catch (Exception $e) {
            Log::error("Exception fetching IP location for {$ip}", [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    private function isLocalIp(string $ip): bool
    {
        // Check for localhost, private networks, etc.
        return in_array($ip, ['127.0.0.1', '::1', 'localhost']) ||
            filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }

    private function isHostingProvider(string $isp): bool
    {
        $hostingKeywords = [
            'aws', 'amazon', 'google', 'microsoft', 'azure', 'digitalocean',
            'linode', 'vultr', 'hetzner', 'ovh', 'cloudflare', 'hosting',
            'datacenter', 'server', 'cloud', 'vps'
        ];

        $ispLower = strtolower($isp);

        foreach ($hostingKeywords as $keyword) {
            if (str_contains($ispLower, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function calculateRiskScore(array $ipData, array|null $location): int
    {
        $score = 0;

        // High error rate
        $errorRate = (float)str_replace('%', '', $ipData['error_rate']);
        if ($errorRate > 50) {
            $score += 30;
        } elseif ($errorRate > 25) {
            $score += 20;
        } elseif ($errorRate > 10) {
            $score += 10;
        }

        // High request volume
        if ($ipData['requests'] > 1000) {
            $score += 25;
        } elseif ($ipData['requests'] > 500) {
            $score += 15;
        } elseif ($ipData['requests'] > 100) {
            $score += 10;
        }

        // Many unique endpoints (potential scanning)
        if ($ipData['unique_endpoints'] > 20) {
            $score += 20;
        } elseif ($ipData['unique_endpoints'] > 10) {
            $score += 10;
        }

        // Hosting provider (common for bots)
        if ($this->isHostingProvider($location['isp'] ?? '')) {
            $score += 15;
        }

        // Known risky countries (adjust as needed)
        $riskyCountries = ['CN', 'RU', 'KP']; // Example
        if (in_array($location['country_code'] ?? '', $riskyCountries, true)) {
            $score += 10;
        }

        return min($score, 100); // Cap at 100
    }

    /**
     * Bulk lookup multiple IPs at once (for future optimization)
     * @unused
     */
    public function bulkLookup(array $ips): array
    {
        $results = [];

        // Process in chunks to avoid overwhelming the API
        $chunks = array_chunk($ips, 10);

        foreach ($chunks as $chunk) {
            foreach ($chunk as $ip) {
                $results[$ip] = $this->getLocationData($ip);

                // Small delay to be nice to the free API
                usleep(100000); // 0.1 second
            }
        }

        return $results;
    }
}
