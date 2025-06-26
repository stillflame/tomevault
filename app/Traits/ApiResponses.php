<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

trait ApiResponses
{
    protected function success(mixed $data = null, string|null $message = null, array|null $additionalMeta = null, int $statusCode = 200): JsonResponse
    {
        $data ??= [];
        $additionalMeta ??= [];
        if ($data instanceof JsonResource) {
            $data = $data->toArray(request());
        }

        $responseData = $this->prepareResponseData($data);
        $meta = $this->buildMeta($statusCode, $additionalMeta, $responseData['timestamps']);

        $response = [
            'data' => $responseData['data'],
            'meta' => $meta,
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        return response()->json($response, $statusCode);
    }

    protected function ok(mixed $data = null, string|null $message = null): JsonResponse
    {
        $data ??= [];
        return $this->success($data, $message);
    }

    protected function error(string $message, int $statusCode): JsonResponse
    {
        $meta = $this->buildMeta($statusCode, [], [
            'timestamp' => now()->toIso8601String(),
            'response_time_ms' => $this->getResponseTime(),
        ]);

        return response()->json(compact('message', 'meta'), $statusCode);
    }

    private function prepareResponseData(mixed $data): array
    {
        $timestamps = [
            'timestamp' => now()->toIso8601String(),
            'response_time_ms' => $this->getResponseTime(),
        ];

        if (is_array($data)) {
            $createdAt = data_get($data, 'created_at');
            $updatedAt = data_get($data, 'updated_at');

            if ($createdAt !== null) {
                $timestamps['created_at'] = $this->formatTimestamp($createdAt);
                data_forget($data, 'created_at');
            }

            if ($updatedAt !== null) {
                $timestamps['updated_at'] = $this->formatTimestamp($updatedAt);
                data_forget($data, 'updated_at');
            }
        }

        return compact('data', 'timestamps');
    }

    private function buildMeta(int $statusCode, array $additionalMeta, array $timestamps): array
    {
        return array_merge(
            ['status' => $statusCode],
            $additionalMeta,
            compact('timestamps')
        );
    }

    private function formatTimestamp($timestamp): string
    {
        if ($timestamp instanceof Carbon) {
            return $timestamp->toIso8601String();
        }

        if (is_string($timestamp)) {
            return Carbon::parse($timestamp)->toIso8601String();
        }

        return (string)$timestamp;
    }

    private function getResponseTime(): float
    {
        if (defined('LARAVEL_START')) {
            return round((microtime(true) - LARAVEL_START) * 1000, 2);
        }

        return round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2);
    }
}
