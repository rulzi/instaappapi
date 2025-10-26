<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;

class ApiResponseService
{
    /**
     * Success response
     */
    public static function success($data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Error response
     */
    public static function error(string $message = 'Error', int $status = 400, $data = null): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Validation error response
     */
    public static function validationError($errors, string $message = 'Validation failed'): JsonResponse
    {
        return response()->json([
            'status' => 422,
            'message' => $message,
            'data' => [
                'errors' => $errors
            ]
        ], 422);
    }

    /**
     * Unauthorized response
     */
    public static function unauthorized(string $message = 'Unauthorized', $data = null): JsonResponse
    {
        return response()->json([
            'status' => 401,
            'message' => $message,
            'data' => $data
        ], 401);
    }

    /**
     * Forbidden response
     */
    public static function forbidden(string $message = 'Forbidden', $data = null): JsonResponse
    {
        return response()->json([
            'status' => 403,
            'message' => $message,
            'data' => $data
        ], 403);
    }

    /**
     * Not found response
     */
    public static function notFound(string $message = 'Not found', $data = null): JsonResponse
    {
        return response()->json([
            'status' => 404,
            'message' => $message,
            'data' => $data
        ], 404);
    }

    /**
     * Server error response
     */
    public static function serverError(string $message = 'Internal server error', $data = null): JsonResponse
    {
        return response()->json([
            'status' => 500,
            'message' => $message,
            'data' => $data
        ], 500);
    }

    /**
     * Created response
     */
    public static function created($data = null, string $message = 'Created successfully'): JsonResponse
    {
        return response()->json([
            'status' => 201,
            'message' => $message,
            'data' => $data
        ], 201);
    }

    /**
     * No content response
     */
    public static function noContent(string $message = 'No content'): JsonResponse
    {
        return response()->json([
            'status' => 204,
            'message' => $message,
            'data' => null
        ], 204);
    }
}
