<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response;

if (! function_exists('jsonResponseFlags')) {
    function jsonResponseFlags(): int
    {
        return JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION;
    }
}

if (! function_exists('respondWithData')) {
    /**
     * Success response with a data payload.
     */
    function respondWithData(mixed $data, ?string $message = null, int $statusCode = Response::HTTP_OK): JsonResponse
    {
        $payload = [];

        if ($message !== null) {
            $payload['message'] = $message;
        }

        $payload['data'] = $data;

        return response()->json($payload, $statusCode, [], jsonResponseFlags());
    }
}

if (! function_exists('respondWithPaginatedData')) {
    /**
     * Success response for a paginated API resource collection.
     */
    function respondWithPaginatedData(ResourceCollection $collection, ?string $message = null, int $statusCode = Response::HTTP_OK): JsonResponse
    {
        $paginated = $collection->response()->getData(true);

        $payload = [];

        if ($message !== null) {
            $payload['message'] = $message;
        }

        $payload['data'] = $paginated['data'] ?? [];
        $payload['meta'] = $paginated['meta'] ?? [];
        $payload['links'] = $paginated['links'] ?? [];

        return response()->json($payload, $statusCode, [], jsonResponseFlags());
    }
}

if (! function_exists('respondWithMessage')) {
    /**
     * Success response with a message only.
     */
    function respondWithMessage(string $message, int $statusCode = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $statusCode, [], jsonResponseFlags());
    }
}

if (! function_exists('respondError')) {
    /**
     * Error response.
     */
    function respondError(string $error, int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        return response()->json([
            'message' => $error,
        ], $statusCode, [], jsonResponseFlags());
    }
}
