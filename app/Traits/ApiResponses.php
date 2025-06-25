<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use SensitiveParameter;

trait ApiResponses
{
    protected function ok($data = [], $message = null): JsonResponse
    {
        return $this->success($data, $message);
    }


    protected function success($data = [], $message = null, $additionalDetails = null, $statusCode = 200): JsonResponse
    {
        $response = [
            'data' => $data,
            'status' => $statusCode,
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        if ($additionalDetails !== null) {
            $response['meta'] = $additionalDetails;
        }

        return response()->json($response, $statusCode);

    }

    protected function error($message, #[SensitiveParameter] $statusCode): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'status' => $statusCode
        ], $statusCode);
    }
}
