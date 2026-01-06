<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class IdempotencyKeyViolationException extends Exception
{
    public function render($request): JsonResponse
    {
        return response()->json([
            'error' => 'Idempotency Key Violation',
            'message' => $this->getMessage() ?: 'This idempotency key has already been used with different transaction parameters.',
        ], 409);
    }
}

