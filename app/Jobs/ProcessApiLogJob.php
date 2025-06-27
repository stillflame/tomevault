<?php

namespace App\Jobs;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use App\Models\ApiLog;
use Throwable;

class ProcessApiLogJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 30;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [10, 30, 60]; // Retry after 10s, then 30s, then 60s
    }

    public function __construct(
        private readonly array          $logData,
        private readonly string         $logLevel,
        private readonly Throwable|null $exception = null
    )
    {
        // Set queue for production optimization
        $this->onQueue('logging');
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        try {
            // Log to database
            $this->logToDatabase($this->logData);

            // Log to file
            $this->logToFile($this->logData, $this->logLevel, $this->exception);

            // Log security events
            $this->logSecurityEvents($this->logData);

            // Log performance issues
            $this->logPerformanceIssues($this->logData['response_time_ms'], $this->logData['endpoint']);

        } catch (Exception $e) {
            Log::error('Failed to process API log job', [
                'error' => $e->getMessage(),
                'request_id' => $this->logData['request_id'] ?? 'unknown',
                'job_id' => $this->job?->getJobId(),
            ]);

            // Re-throw to trigger retry
            throw $e;
        }
    }

    private function logToDatabase(array $logData): void
    {
        // Only store essential data in production for performance
        ApiLog::create([
            'request_id' => $logData['request_id'],
            'method' => $logData['method'],
            'url' => $logData['url'],
            'endpoint' => $logData['endpoint'],
            'status_code' => $logData['status_code'],
            'response_time_ms' => $logData['response_time_ms'],
            'ip_address' => $logData['ip_address'],
            'user_id' => $logData['user_id'],
            'request_headers' => $logData['request_headers'], // ✅ ADDED
            'user_type' => $logData['user_type'],
            'user_agent' => $logData['user_agent'],
            'request_data' => $logData['request_data'],
            'response_data' => $logData['response_data'],
            'response_size' => $logData['response_size'], // ✅ ADDED
            'error_message' => $logData['error_message'],
            'error_context' => $logData['error_context'], // ✅ ADDED
            'log_level' => $logData['log_level'],
            'metadata' => $logData['metadata'], // ✅ ADDED
            'created_at' => now(),
        ]);
    }

    private function logToFile(array $logData, string $logLevel, Throwable|null $exception): void
    {
        $context = [
            'request_id' => $logData['request_id'],
            'method' => $logData['method'],
            'endpoint' => $logData['endpoint'],
            'status' => $logData['status_code'],
            'response_time' => $logData['response_time_ms'] . 'ms',
            'ip' => $logData['ip_address'],
            'user_id' => $logData['user_id'],
        ];

        if ($exception) {
            $context['error'] = $exception->getMessage();
            $context['file'] = $exception->getFile();
            $context['line'] = $exception->getLine();
        }

        // Log to API file
        Log::channel('api')->{$logLevel}('API Request', $context);
    }

    private function logSecurityEvents(array $logData): void
    {
        $endpoint = $logData['endpoint'];
        $statusCode = $logData['status_code'];
        $method = $logData['method'];
        $ip = $logData['ip_address'];
        $userAgent = $logData['user_agent'];

        // Log suspicious patterns
        if ($statusCode === 401 || $statusCode === 403) {
            Log::warning('Security: Unauthorized access attempt', [
                'request_id' => $logData['request_id'],
                'endpoint' => $endpoint,
                'method' => $method,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'status_code' => $statusCode,
            ]);
        }

        // Log potential attacks
        if (!$logData['user_id'] && str_contains(strtolower($endpoint), 'admin')) {
            Log::warning('Security: Unauthenticated admin access attempt', [
                'request_id' => $logData['request_id'],
                'endpoint' => $endpoint,
                'ip' => $ip,
                'user_agent' => $userAgent,
            ]);
        }
    }

    private function logPerformanceIssues(float $responseTime, string $endpoint): void
    {
        $slowThreshold = config('logging.slow_request_threshold', 1000);
        $verySlowThreshold = config('logging.very_slow_request_threshold', 5000);

        if ($responseTime > $verySlowThreshold) {
            Log::warning('Performance: Very slow API request', [
                'endpoint' => $endpoint,
                'response_time_ms' => $responseTime,
                'threshold' => $verySlowThreshold,
            ]);
        } elseif ($responseTime > $slowThreshold) {
            Log::info('Performance: Slow API request', [
                'endpoint' => $endpoint,
                'response_time_ms' => $responseTime,
                'threshold' => $slowThreshold,
            ]);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('API log job failed permanently', [
            'request_id' => $this->logData['request_id'] ?? 'unknown',
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }
}
