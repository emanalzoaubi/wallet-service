<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BaseController extends Controller
{
    protected function respond($data, $statusCode = Response::HTTP_OK, $headers = []): JsonResponse
    {
        // Add a status field to the response data indicating success
        $responseData = [
            'status' => 'success',
            'data' => $data,
        ];

        // Return the response with appropriate headers and status code
        return response()->json($responseData, $statusCode, $headers);
    }

    protected function respondError($message, $statusCode = Response::HTTP_NOT_FOUND): JsonResponse
    {
        // Add a status field to the response data indicating failure
        $responseData = [
            'status' => 'error',
            'message' => $message,
        ];

        // Return the response with appropriate headers and status code
        return response()->json($responseData, $statusCode);
    }
}
