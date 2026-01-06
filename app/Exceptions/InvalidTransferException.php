<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class InvalidTransferException extends Exception
{
    public function render($request): JsonResponse
    {
        return response()->json([
            'error' => 'Invalid Transfer',
            'message' => $this->getMessage() ?: 'The transfer operation is invalid.',
        ], 422);
    }
}

