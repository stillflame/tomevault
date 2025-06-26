<?php

namespace App\Http\Middleware;

use App\Services\ApiLoggingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ApiLoggingMiddleware
{
    public function __construct(
        private readonly ApiLoggingService $loggingService
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Add request ID to headers for tracking
        $request->headers->set('X-Request-ID', $this->loggingService->getRequestId());

        try {
            $response = $next($request);

            // Log successful request
            $this->loggingService->logRequest($request, $response);

            // Add request ID to response headers
            $response->headers->set('X-Request-ID', $this->loggingService->getRequestId());

            return $response;

        } catch (Throwable $exception) {
            // Log failed request
            $this->loggingService->logRequest($request, null, $exception);

            throw $exception;
        }
    }
}
