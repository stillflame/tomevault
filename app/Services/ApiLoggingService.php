<?php

namespace App\Services;

use App\Models\ApiLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ApiLoggingService
{
    private string $requestId;
    private float $startTime;

    public function __construct()
    {
        $this->requestId = request()->header('X-Request-ID') ?? Str::uuid()->toString();
        $this->startTime = microtime(true);
    }

    public function logRequest(Request $request, $response = null, ?\Throwable $exception = null): void
    {
        try {
            $responseTime = $this->getResponseTime();
            $statusCode = $response?->getStatusCode() ?? 500;
            $logLevel = $this->determineLogLevel($statusCode, $responseTime, $exception);

            $logData = [
                'request_id' => $this->requestId,
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'endpoint' => $request->path(),
                'ip_address' => $this->getClientIp($request),
                'user_agent' => $request->userAgent(),
                'user_id' => auth()->id(),
                'user_type' => auth()->user()?->type ?? 'anonymous',
                'status_code' => $statusCode,
                'response_time_ms' => $responseTime,
                'response_size' => $response ? strlen($response->getContent()) : null,
                'request_headers' => $this->sanitizeHeaders($request->headers->all()),
                'request_data' => $this->sanitizeRequestData($request->all()),
                'response_data' => $this->sanitizeResponseData($response),
                'cache_hit' => $request->header('X-Cache-Hit') === 'true',
                'log_level' => $logLevel,
                'error_message' => $exception?->getMessage(),
                'error_context' => $exception ? $this->buildErrorContext($exception) : null,
                'metadata' => $this->buildMetadata($request, $response),
            ];

            // Log to database
            $this->logToDatabase($logData);

            // Log to Laravel log files
            $this->logToFile($logData, $logLevel, $exception);

            // Log security events
            $this->logSecurityEvents($request, $statusCode);

            // Log performance issues
            $this->logPerformanceIssues($responseTime, $request->path());

        } catch (\Exception $e) {
            // Fail silently but log the logging error
            Log::error('Failed to log API request', [
                'error' => $e->getMessage(),
                'request_id' => $this->requestId,
            ]);
        }
    }

    private function logToDatabase(array $logData): void
    {
        try {
            if (config('logging.log_to_database', true)) {
                // Direct insert (immediate) - good for testing
                ApiLog::create($logData);

                // Queue version (better for production)
                // if (config('logging.database_logging_queue')) {
                //     dispatch(function () use ($logData) {
                //         ApiLog::create($logData);
                //     })->onQueue('logging');
                // } else {
                //     ApiLog::create($logData);
                // }
            }
        } catch (\Exception $e) {
            // If DB logging fails, at least log to file
            Log::error('Database logging failed', [
                'error' => $e->getMessage(),
                'request_id' => $logData['request_id'] ?? 'unknown',
            ]);
        }
    }

    private function logToFile(array $logData, string $level, ?\Throwable $exception): void
    {
        $context = [
            'request_id' => $logData['request_id'],
            'method' => $logData['method'],
            'endpoint' => $logData['endpoint'],
            'status' => $logData['status_code'],
            'response_time' => $logData['response_time_ms'] . 'ms',
            'user_id' => $logData['user_id'],
            'ip' => $logData['ip_address'],
        ];

        if ($exception) {
            $errorContext = array_merge($context, [
                'exception' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);

            // Log to multiple channels
            Log::error('API Error', $errorContext);

            // Also send to Logtail if configured
            if (config('logging.channels.logtail.handler_with.token')) {
                Log::channel('logtail')->error('API Error', $errorContext);
            }
        } else {
            // Log successful requests
            Log::{$level}('API Request', $context);

            // Also send to Logtail if configured
            if (config('logging.channels.logtail.handler_with.token')) {
                Log::channel('logtail')->{$level}('API Request', $context);
            }
        }
    }

    private function logSecurityEvents(Request $request, int $statusCode): void
    {
        // Failed authentication
        if ($statusCode === 401) {
            Log::warning('Failed authentication attempt', [
                'ip' => $this->getClientIp($request),
                'endpoint' => $request->path(),
                'user_agent' => $request->userAgent(),
                'request_id' => $this->requestId,
            ]);
        }

        // Potential SQL injection
        if ($this->detectSqlInjection($request)) {
            Log::critical('Potential SQL injection attempt', [
                'ip' => $this->getClientIp($request),
                'endpoint' => $request->path(),
                'request_data' => $request->all(),
                'request_id' => $this->requestId,
            ]);
        }

        // Unusual request patterns
        if ($this->detectUnusualPatterns($request)) {
            Log::warning('Unusual request pattern detected', [
                'ip' => $this->getClientIp($request),
                'endpoint' => $request->path(),
                'user_agent' => $request->userAgent(),
                'request_id' => $this->requestId,
            ]);
        }
    }

    private function logPerformanceIssues(float $responseTime, string $endpoint): void
    {
        // Only log performance issues if enabled
        if (!config('logging.performance_logging', false)) {
            return;
        }

        if ($responseTime > config('logging.slow_request_threshold', 1000)) {
            $context = [
                'endpoint' => $endpoint,
                'response_time_ms' => $responseTime,
                'request_id' => $this->requestId,
                'threshold' => 'slow',
                'severity' => 'warning',
            ];

            // Log to dedicated performance channel
            Log::channel('performance')->warning('Slow API response', $context);
        }

        if ($responseTime > config('logging.very_slow_request_threshold', 5000)) {
            $context = [
                'endpoint' => $endpoint,
                'response_time_ms' => $responseTime,
                'request_id' => $this->requestId,
                'threshold' => 'very_slow',
                'severity' => 'critical',
            ];

            // Log to performance channel AND main stack for critical issues
            Log::channel('performance')->critical('Very slow API response', $context);
            Log::critical('Very slow API response', $context);
        }
    }

    private function getResponseTime(): float
    {
        return round((microtime(true) - $this->startTime) * 1000, 2);
    }

    private function determineLogLevel(int $statusCode, float $responseTime, ?\Throwable $exception): string
    {
        if ($exception || $statusCode >= 500) return 'error';
        if ($statusCode >= 400) return 'warning';
        if ($responseTime > 5000) return 'warning';
        return 'info';
    }

    private function getClientIp(Request $request): string
    {
        return $request->header('X-Forwarded-For')
            ?? $request->header('X-Real-IP')
            ?? $request->ip();
    }

    private function sanitizeHeaders(array $headers): array
    {
        $sensitive = ['authorization', 'x-api-key', 'cookie', 'x-auth-token'];

        foreach ($sensitive as $header) {
            if (isset($headers[$header])) {
                $headers[$header] = ['[REDACTED]'];
            }
        }

        return $headers;
    }

    private function sanitizeRequestData(array $data): ?array
    {
        if (empty($data)) return null;

        $sensitive = ['password', 'token', 'secret', 'key', 'api_key'];

        foreach ($sensitive as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }

    private function sanitizeResponseData($response): ?array
    {
        if (!$response || !method_exists($response, 'getContent')) {
            return null;
        }

        $content = $response->getContent();

        // Only log first 1000 characters to avoid huge logs
        if (strlen($content) > 1000) {
            return ['truncated' => true, 'preview' => substr($content, 0, 1000)];
        }

        // Try to decode JSON response
        $decoded = json_decode($content, true);
        return $decoded ?? ['raw' => $content];
    }

    private function buildErrorContext(\Throwable $exception): array
    {
        return [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => array_slice($exception->getTrace(), 0, 5), // Limit trace size
            'previous' => $exception->getPrevious()?->getMessage(),
        ];
    }

    private function buildMetadata(Request $request, $response): array
    {
        return [
            'memory_usage' => memory_get_peak_usage(true),
            'query_count' => \DB::getQueryLog() ? count(\DB::getQueryLog()) : null,
            'referer' => $request->header('Referer'),
            'locale' => app()->getLocale(),
            'timezone' => config('app.timezone'),
        ];
    }

    private function detectSqlInjection(Request $request): bool
    {
        $patterns = [
            '/union\s+select/i',
            '/drop\s+table/i',
            '/delete\s+from/i',
            '/insert\s+into/i',
            '/update\s+.*set/i',
            '/exec\s*\(/i',
        ];

        $input = json_encode($request->all());

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    private function detectUnusualPatterns(Request $request): bool
    {
        // Check for unusual user agents
        $userAgent = $request->userAgent();
        $suspiciousAgents = ['curl', 'wget', 'python-requests', 'bot', 'crawler'];

        foreach ($suspiciousAgents as $agent) {
            if (stripos($userAgent, $agent) !== false) {
                return true;
            }
        }

        // Check for rapid requests from same IP
        $recentRequests = ApiLog::where('ip_address', $this->getClientIp($request))
            ->where('created_at', '>=', now()->subMinute())
            ->count();

        return $recentRequests > 60; // More than 60 requests per minute
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }
}
