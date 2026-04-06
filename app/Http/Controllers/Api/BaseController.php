<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    /**
     * Send a success response.
     *
     * @param  mixed  $data
     * @param  string  $message
     * @param  int  $code
     * @return JsonResponse
     */
    protected function sendSuccess(mixed $data = [], string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * Send an error response.
     *
     * @param  string  $message
     * @param  int  $code
     * @return JsonResponse
     */
    protected function sendError(string $message, int $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
        ], $code);
    }

    /**
     * Send a created response.
     *
     * @param  mixed  $data
     * @param  string  $message
     * @return JsonResponse
     */
    protected function sendCreated(mixed $data = [], string $message = 'Resource created successfully'): JsonResponse
    {
        return $this->sendSuccess($data, $message, 201);
    }

    /**
     * Send a deleted response.
     *
     * @param  string  $message
     * @return JsonResponse
     */
    protected function sendDeleted(string $message = 'Resource deleted successfully'): JsonResponse
    {
        return $this->sendSuccess([], $message, 200);
    }
}
